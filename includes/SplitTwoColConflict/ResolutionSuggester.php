<?php

namespace TwoColConflict\SplitTwoColConflict;

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use Title;
use TwoColConflict\AnnotatedHtmlDiffFormatter;

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
	 * @param array $a
	 * @param array $b
	 * @return bool
	 */
	private static function identicalCopyBlock( $a, $b ) {
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

		$diffYourLines = $this->diff( $baseLines, $yourLines );
		$diffStoredLines = $this->diff( $baseLines, $storedLines );

		$count = count( $diffYourLines );
		if ( $count !== count( $diffStoredLines ) ) {
			return false;
		}

		// only diffs that contain exactly one addition that is optionally
		// preceded or succeeded by one identical copy line at a time are
		// candidates for the resolution suggestion
		if ( $count === 1 ) {
			$yourLine = $diffYourLines[0];
			$storedLine = $diffStoredLines[0];
		} elseif ( $count === 2 ) {
			// if the diffs contain only two action either the first or the second
			// action must be identical copies
			if ( self::identicalCopyBlock( $diffYourLines[0], $diffStoredLines[0] ) ) {
				$yourLine = $diffYourLines[1];
				$storedLine = $diffStoredLines[1];
			} elseif ( self::identicalCopyBlock( $diffYourLines[1], $diffStoredLines[1] ) ) {
				$yourLine = $diffYourLines[0];
				$storedLine = $diffStoredLines[0];
			} else {
				return false;
			}
		} elseif ( $count === 3 &&
			// if the diffs contain three actions the preceding and succeeding
			// actions must be identical copies
			self::identicalCopyBlock( $diffYourLines[0], $diffStoredLines[0] ) &&
			self::identicalCopyBlock( $diffYourLines[2], $diffStoredLines[2] )
		) {
			$yourLine = $diffYourLines[1];
			$storedLine = $diffStoredLines[1];
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

	/**
	 * @param string[] $fromLines
	 * @param string[] $toLines
	 *
	 * @return array[]
	 */
	private function diff( array $fromLines, array $toLines ) : array {
		return ( new AnnotatedHtmlDiffFormatter() )->format(
			new \Diff( $fromLines, $toLines ) );
	}

}
