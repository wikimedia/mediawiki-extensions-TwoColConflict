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
	 * @param array $a First block to compare
	 * @param array $b Second block
	 * @return bool True if the blocks are both copy blocks, with identical content.
	 */
	private static function isIdenticalCopyBlock( array $a, array $b ) : bool {
		return $a['action'] === 'copy' && $a === $b;
	}

	/**
	 * @param array $a First block to compare
	 * @param array $b Second block
	 * @return bool True if the blocks are both add blocks, at the same line
	 */
	private static function isConflictingAddBlock( array $a, array $b ) {
		return $a['action'] === 'add' && $b['action'] === 'add';
	}

	/**
	 * @param Title $title
	 * @param string[] $storedLines
	 * @param string[] $yourLines
	 * @return TalkPageResolution|null
	 */
	public function getResolutionSuggestion(
		Title $title,
		array $storedLines,
		array $yourLines
	) : ?TalkPageResolution {
		$services = MediaWikiServices::getInstance();
		if ( !$services->getMainConfig()->get( 'TwoColConflictSuggestResolution' ) ||
			!$services->getNamespaceInfo()->isTalk( $title->getNamespace() )
		) {
			return null;
		}

		$baseLines = $this->getBaseRevisionLines();

		$formatter = new AnnotatedHtmlDiffFormatter();
		// TODO: preSaveTransform $yourLines, but not $storedLines
		$diffYourLines = $formatter->format( $baseLines, $yourLines, $yourLines );
		$diffStoredLines = $formatter->format( $baseLines, $storedLines, $storedLines );

		$count = count( $diffYourLines );
		if ( $count !== count( $diffStoredLines ) ) {
			return null;
		}

		// only diffs that contain exactly one addition, that is optionally
		// preceded and/or succeeded by one identical copy line, are
		// candidates for the resolution suggestion

		$diff = [];
		/** @var ?int $spliceIndex */
		$spliceIndex = null;
		// Copy over identical blocks, and splice the two alternatives.
		foreach ( $diffYourLines as $index => $yourLine ) {
			$otherLine = $diffStoredLines[$index];
			if ( self::isIdenticalCopyBlock( $yourLine, $otherLine ) ) {
				// Copy
				$diff[] = $otherLine;
			} elseif ( self::isConflictingAddBlock( $yourLine, $otherLine )
				&& $spliceIndex === null
			) {
				// Splice alternatives
				$spliceIndex = count( $diff );
				$diff[] = $otherLine;
				$diff[] = $yourLine;
			} else {
				return null;
			}
		}
		if ( $spliceIndex === null ) {
			// TODO: I'm not sure yet, but this might be a logic error and should be logged.
			return null;
		}

		return new TalkPageResolution( $diff, $spliceIndex, $spliceIndex + 1 );
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
