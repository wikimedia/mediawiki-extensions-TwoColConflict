<?php

namespace TwoColConflict;

use Diff;
use MediaWiki\Diff\WordAccumulator;
use WordLevelDiff;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class LineBasedUnifiedDiffFormatter {

	/**
	 * @var int
	 */
	private $oldLine;

	/**
	 * @var int
	 */
	private $newLine;

	/**
	 * @param Diff $diff A Diff object.
	 *
	 * @return array[] Associative array showing lists of changes in lines of the original text.
	 * - The array is numbered with the numbers orientating on line numbers from the original or
	 *   left side of the diff. Since the right side can hold more lines than the left, one line
	 *   in the array can hold at least one delete, change or copy as well as an add action.
	 */
	public function format( Diff $diff ) {
		$changes = [];
		$this->oldLine = 0;
		$this->newLine = 0;

		foreach ( $diff->getEdits() as $edit ) {
			switch ( $edit->getType() ) {
				case 'add':
					$this->trackAdd(
						$changes,
						$this->oldLine,
						count( $edit->getClosing() ),
						'<ins class="mw-twocolconflict-diffchange">' .
							$this->composeLines( $edit->getClosing() ) . '</ins>'
					);
					break;

				case 'delete':
					$this->trackDelete(
						$changes,
						$this->oldLine,
						count( $edit->getOrig() ),
						'<del class="mw-twocolconflict-diffchange">' .
							$this->composeLines( $edit->getOrig() ) . '</del>'
					);
					break;

				case 'change':
					// Required because trackDelete() will (and should) increase $this->oldLine.
					$originalLineNumber = $this->oldLine;
					$wordLevelDiff = $this->rTrimmedWordLevelDiff( $edit->getOrig(), $edit->getClosing() );

					$this->trackDelete(
						$changes,
						$originalLineNumber,
						count( $edit->getOrig() ),
						$this->getOriginalInlineDiff( $wordLevelDiff )
					);
					$this->trackAdd(
						$changes,
						$originalLineNumber,
						count( $edit->getClosing() ),
						$this->getClosingInlineDiff( $wordLevelDiff )
					);
					break;

				case 'copy':
					$changes[$this->oldLine][] = [
						'action' => 'copy',
						// TODO: The "copy" element is not used in split mode, and can be removed
						// when the inline mode is gone.
						'copy' => htmlspecialchars( implode( "\n", $edit->getOrig() ) ),
						'oldline' => $this->oldLine,
						'count' => count( $edit->getOrig() ),
					];
					$this->oldLine += count( $edit->getOrig() );
					$this->newLine += count( $edit->getOrig() );
					break;
			}
		}

		return $changes;
	}

	/**
	 * @param string[] $before
	 * @param string[] $after
	 *
	 * @return WordLevelDiff
	 */
	private function rTrimmedWordLevelDiff( array $before, array $after ) {
		end( $before );
		end( $after );
		$this->commonRTrim( $before[key( $before )], $after[key( $after )] );
		return new WordLevelDiff( $before, $after );
	}

	/**
	 * @param string $before
	 * @param string $after
	 */
	private function commonRTrim( &$before, &$after ) {
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
	 * Will increase $this->oldLine by $lineCount.
	 *
	 * @param array[] &$changes
	 * @param int $index
	 * @param int $lineCount Number of source code lines in the $diffHtml
	 * @param string $diffHtml HTML
	 */
	private function trackDelete( array &$changes, $index, $lineCount, $diffHtml ) {
		$changes[$index][] = [
			'action' => 'delete',
			'old' => $diffHtml,
			'oldline' => $this->oldLine,
			'count' => $lineCount,
		];
		$this->oldLine += $lineCount;
	}

	/**
	 * Will increase $this->newLine by $lineCount.
	 *
	 * @param array[] &$changes
	 * @param int $index
	 * @param int $lineCount Number of source code lines in the $diffHtml
	 * @param string $diffHtml HTML
	 */
	private function trackAdd(
		array &$changes,
		$index,
		$lineCount,
		$diffHtml
	) {
		$changes[$index][] = [
			'action' => 'add',
			'new' => $diffHtml,
			'newline' => $this->newLine,
			'count' => $lineCount,
		];
		$this->newLine += $lineCount;
	}

	/**
	 * Composes lines from a WordLevelDiff and marks removed words.
	 *
	 * @param WordLevelDiff $diff Diff on word level.
	 *
	 * @return string Composed string with marked lines.
	 */
	private function getOriginalInlineDiff( WordLevelDiff $diff ) {
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
	private function getClosingInlineDiff( WordLevelDiff $diff ) {
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
	private function getWordAccumulator() {
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
	private function composeLines( array $lines ) {
		return htmlspecialchars( implode( "\n", array_map(
			function ( $line ) {
				// Replace empty lines with a non-breaking space
				return $line === '' ? "\u{00A0}" : $line;
			},
			$lines
		) ) );
	}

}
