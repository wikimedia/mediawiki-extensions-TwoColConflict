<?php

namespace TwoColConflict;

use Diff;
use MediaWiki\Diff\WordAccumulator;
use WordLevelDiff;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class AnnotatedHtmlDiffFormatter {

	/**
	 * @param string[] $oldLines
	 * @param string[] $newLines
	 * @param string[] $preSaveTransformedLines
	 *
	 * @return array[] List of changes, each of which include an HTML representation of the diff,
	 *     and the original wikitext.
	 * TODO: "preSavedTransformedLines" is still warty.
	 */
	public function format(
		array $oldLines,
		array $newLines,
		array $preSaveTransformedLines
	) : array {
		$changes = [];
		$oldLine = 0;
		$newLine = 0;
		$diff = new Diff( $oldLines, $preSaveTransformedLines );

		foreach ( $diff->getEdits() as $edit ) {
			switch ( $edit->getType() ) {
				case 'add':
					$changes[] = [
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => '',
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">' .
							$this->composeLines( $edit->getClosing() ) . '</ins>',
						'newtext' => implode( "\n",
							array_slice( $newLines, $newLine, $edit->nclosing() ) ),
					];
					break;

				case 'delete':
					$changes[] = [
						'action' => 'delete',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">' .
							$this->composeLines( $edit->getOrig() ) . '</del>',
						'oldtext' => implode( "\n",
							array_slice( $oldLines, $oldLine, $edit->norig() ) ),
						'newhtml' => "\u{00A0}",
						'newtext' => '',
					];
					break;

				case 'change':
					$wordLevelDiff = $this->rTrimmedWordLevelDiff( $edit->getOrig(), $edit->getClosing() );
					$changes[] = [
						'action' => 'change',
						'oldhtml' => $this->getOriginalInlineDiff( $wordLevelDiff ),
						'newhtml' => $this->getClosingInlineDiff( $wordLevelDiff ),
						'oldtext' => implode( "\n",
							array_slice( $oldLines, $oldLine, $edit->norig() ) ),
						'newtext' => implode( "\n",
							array_slice( $newLines, $newLine, $edit->nclosing() ) ),
					];
					break;

				case 'copy':
					$changes[] = [
						'action' => 'copy',
						'copytext' => $this->composeLines( $edit->getOrig() ),
					];
					break;
			}

			$oldLine += $edit->norig();
			$newLine += $edit->nclosing();
		}

		return $changes;
	}

	/**
	 * @param string[] $before
	 * @param string[] $after
	 *
	 * @return WordLevelDiff
	 */
	private function rTrimmedWordLevelDiff( array $before, array $after ) : WordLevelDiff {
		end( $before );
		end( $after );
		$this->commonRTrim( $before[key( $before )], $after[key( $after )] );
		return new WordLevelDiff( $before, $after );
	}

	/**
	 * @param string &$before
	 * @param string &$after
	 */
	private function commonRTrim( string &$before, string &$after ) {
		$uncommonBefore = strlen( $before );
		$uncommonAfter = strlen( $after );
		while ( $uncommonBefore > 0 &&
			$uncommonAfter > 0 &&
			$before[$uncommonBefore - 1] === $after[$uncommonAfter - 1] &&
			ctype_space( $after[$uncommonAfter - 1] )
		) {
			$uncommonBefore--;
			$uncommonAfter--;
		}
		$before = substr( $before, 0, $uncommonBefore );
		$after = substr( $after, 0, $uncommonAfter );
	}

	/**
	 * Composes lines from a WordLevelDiff and marks removed words.
	 *
	 * @param WordLevelDiff $diff Diff on word level.
	 *
	 * @return string Composed string with marked lines.
	 */
	private function getOriginalInlineDiff( WordLevelDiff $diff ) : string {
		$wordAccumulator = $this->getWordAccumulator();

		foreach ( $diff->getEdits() as $edit ) {
			if ( $edit->type === 'copy' ) {
				$wordAccumulator->addWords( $edit->orig );
			} elseif ( $edit->orig ) {
				$wordAccumulator->addWords( $edit->orig, 'del' );
			}
		}
		return implode( "\n", $wordAccumulator->getLines() );
	}

	/**
	 * Composes lines from a WordLevelDiff and marks added words.
	 *
	 * @param WordLevelDiff $diff Diff on word level.
	 *
	 * @return string Composed string with marked lines.
	 */
	private function getClosingInlineDiff( WordLevelDiff $diff ) : string {
		$wordAccumulator = $this->getWordAccumulator();

		foreach ( $diff->getEdits() as $edit ) {
			if ( $edit->type === 'copy' ) {
				$wordAccumulator->addWords( $edit->closing );
			} elseif ( $edit->closing ) {
				$wordAccumulator->addWords( $edit->closing, 'ins' );
			}
		}
		return implode( "\n", $wordAccumulator->getLines() );
	}

	/**
	 * @return WordAccumulator
	 */
	private function getWordAccumulator() : WordAccumulator {
		$wordAccumulator = new WordAccumulator;
		$wordAccumulator->insClass = ' class="mw-twocolconflict-diffchange"';
		$wordAccumulator->delClass = ' class="mw-twocolconflict-diffchange"';
		return $wordAccumulator;
	}

	/**
	 * @param string[] $lines Lines that should be composed.
	 *
	 * @return string HTML
	 */
	private function composeLines( array $lines ) : string {
		return htmlspecialchars( implode( "\n", array_map(
			function ( string $line ) : string {
				// Replace empty lines with a non-breaking space
				return $line === '' ? "\u{00A0}" : $line;
			},
			$lines
		) ) );
	}

}
