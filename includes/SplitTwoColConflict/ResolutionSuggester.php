<?php

namespace TwoColConflict\SplitTwoColConflict;

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use Title;
use TwoColConflict\LineBasedUnifiedDiffFormatter;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class ResolutionSuggester {

	/**
	 * @var RevisionRecord
	 */
	private $baseRevision;

	/**
	 * @var string
	 */
	private $contentFormat;

	/**
	 * @param RevisionRecord|null $baseRevision
	 * @param string $contentFormat
	 */
	public function __construct( ?RevisionRecord $baseRevision, string $contentFormat ) {
		$this->baseRevision = $baseRevision;
		$this->contentFormat = $contentFormat;
	}

	/**
	 * @param int $idx
	 * @param array $diffALines
	 * @param array $diffBLines
	 * @return bool
	 */
	private static function identicalCopyBlock( $idx, $diffALines, $diffBLines ) {
		$a = array_slice( $diffALines, $idx, 1 )[0];
		$b = array_slice( $diffBLines, $idx, 1 )[0];
		return $a['action'] === 'copy' && $a === $b;
	}

	/**
	 * @param Title $title
	 * @param string[] $yourLines
	 * @param string[] $storedLines
	 * @return bool
	 */
	public function getResolutionSuggestion(
		Title $title,
		array $yourLines,
		array $storedLines
	) : bool {
		$services = MediaWikiServices::getInstance();
		if ( !$services->getMainConfig()->get( 'TwoColConflictSuggestResolution' ) ||
			!$services->getNamespaceInfo()->isTalk( $title->getNamespace() )
		) {
			return false;
		}

		$baseLines = $this->getBaseRevisionLines();
		// if the base version is empty there's an addition from both sides
		// we should be able to suggest a resolution then
		if ( $baseLines === [] ) {
			return true;
		}

		$diffYourLines = ( new LineBasedUnifiedDiffFormatter() )->format(
			new \Diff( $baseLines, $yourLines )
		);
		$diffStoredLines = ( new LineBasedUnifiedDiffFormatter() )->format(
			new \Diff( $baseLines, $storedLines )
		);

		$diffYourLinesSize = count( $diffYourLines );
		$diffStoredLinesSize = count( $diffStoredLines );

		// only diffs that contain exactly one addition that is optionally
		// preceded or succeeded by one identical copy line at a time are
		// candidates for the resolution suggestion
		if ( $diffYourLinesSize !== $diffStoredLinesSize || $diffYourLinesSize > 3 ) {
			return false;
		}

		// for each case identify the lines on both sides that should be
		// part of the suggestion
		if ( $diffYourLinesSize === 1 ) {
			$yourLine = array_pop( $diffYourLines );
			$storedLine = array_pop( $diffStoredLines );
		} elseif ( $diffYourLinesSize === 2 ) {
			// if the diffs contain only two action either the first or the second
			// action must be identical copies
			if ( self::identicalCopyBlock( 0, $diffYourLines, $diffStoredLines ) ) {
				$yourLine = array_pop( $diffYourLines );
				$storedLine = array_pop( $diffStoredLines );
			} elseif ( self::identicalCopyBlock( 1, $diffYourLines, $diffStoredLines ) ) {
				$yourLine = array_shift( $diffYourLines );
				$storedLine = array_shift( $diffStoredLines );
			} else {
				return false;
			}
		} elseif ( $diffYourLinesSize === 3 &&
			// if the diffs contain three actions the preceding and succeeding
			// actions must be identical copies
			self::identicalCopyBlock( 0, $diffYourLines, $diffStoredLines ) &&
			self::identicalCopyBlock( 2, $diffYourLines, $diffStoredLines )
		) {
			$yourLine = array_slice( $diffYourLines, 1, 1 )[0];
			$storedLine = array_slice( $diffStoredLines, 1, 1 )[0];
		} else {
			return false;
		}

		// we are only suggesting a resolution if we have two additions in the same line
		if ( $yourLine['action'] !== 'add' ||
			$storedLine['action'] !== 'add' ||
			$yourLine['newline'] !== $storedLine['newline']
		) {
			return false;
		}

		// TODO in theory we could return what's needed to show the suggestion here
		return true;
	}

	/**
	 * @return string[]
	 */
	private function getBaseRevisionLines() {
		if ( !$this->baseRevision ) {
			return [];
		}

		$baseContent = $this->baseRevision->getContent( SlotRecord::MAIN );
		if ( !$baseContent ) {
			return [];
		}

		$baseText = $baseContent->serialize( $this->contentFormat );
		if ( !$baseText ) {
			return [];
		}

		return SplitConflictUtils::splitText( $baseText );
	}

}
