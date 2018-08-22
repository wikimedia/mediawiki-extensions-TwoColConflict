<?php

namespace TwoColConflict;

use Diff;
use DiffFormatter;
use MediaWiki\Diff\WordAccumulator;
use WordLevelDiff;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class LineBasedUnifiedDiffFormatter extends DiffFormatter {

	/**
	 * @var string String added to <ins> tags.
	 */
	public $insClass = ' class="diffchange"';

	/**
	 * @var string String added to <del> tags.
	 */
	public $delClass = ' class="diffchange"';

	/**
	 * @var int
	 */
	private $oldline;

	/**
	 * @var int
	 */
	private $newline;

	/**
	 * @var array[]
	 */
	private $retval;

	/**
	 * @param Diff $diff A Diff object.
	 *
	 * @return array[] Associative array showing lists of changes in lines of the original text.
	 * - The array is numbered with the numbers orientating on line numbers from the original or
	 *   left side of the diff. Since the right side can hold more lines than the left, one line
	 *   in the array can hold at least one delete, change or copy as well as an add action.
	 */
	public function format( $diff ) {
		$this->oldline = 0;
		$this->newline = 0;
		$this->retval = [];

		foreach ( $diff->getEdits() as $edit ) {
			switch ( $edit->getType() ) {
				case 'add':
					$this->addLines( $edit->getClosing() );
					$this->newline += count( $edit->getClosing() );
					break;
				case 'delete':
					$this->deleteLines( $edit->getOrig() );
					$this->oldline += count( $edit->getOrig() );
					break;
				case 'change':
					$wordLevelDiff = $this->rTrimmedWordLevelDiff( $edit->getOrig(), $edit->getClosing() );

					$this->retval[$this->oldline][] = [
						'action' => 'delete',
						'old' => $this->getOriginalInlineDiff( $wordLevelDiff ),
						'oldline' => $this->oldline,
						'count' => count( $edit->getOrig() ),
					];
					$this->retval[$this->oldline][] = [
						'action' => 'add',
						'new' => $this->getClosingInlineDiff( $wordLevelDiff ),
						'newline' => $this->newline,
						'count' => count( $edit->getClosing() ),
					];

					$this->oldline += count( $edit->getOrig() );
					$this->newline += count( $edit->getClosing() );
					break;
				case 'copy':
					$this->copyLines( $edit->getOrig() );
					$this->oldline += count( $edit->getOrig() );
					$this->newline += count( $edit->getOrig() );
					break;
			}
		}

		return $this->retval;
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
	 * @param string[] $lines Lines that should be marked deleted.
	 */
	private function deleteLines( array $lines ) {
		$this->retval[$this->oldline][] = [
			'action' => 'delete',
			'old' => "<del{$this->delClass}>" . $this->composeLines( $lines ) . '</del>',
			'oldline' => $this->oldline,
			'count' => count( $lines ),
		];
	}

	/**
	 * @param string[] $lines Lines that should be marked as added.
	 */
	private function addLines( array $lines ) {
		$this->retval[$this->oldline][] = [
			'action' => 'add',
			'new' => "<ins{$this->insClass}>" . $this->composeLines( $lines ) . '</ins>',
			'newline' => $this->newline,
			'count' => count( $lines ),
		];
	}

	/**
	 * @param string[] $lines Lines that should be copied.
	 */
	private function copyLines( array $lines ) {
		$this->retval[$this->oldline][] = [
			'action' => 'copy',
			'copy' => $this->composeLines( $lines, false ),
			'oldline' => $this->oldline,
			'newline' => $this->newline
		];
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
		$wordAccumulator->insClass = $this->insClass;
		$wordAccumulator->delClass = $this->delClass;
		return $wordAccumulator;
	}

	/**
	 * @param string[] $lines Lines that should be composed.
	 * @param boolean $replaceEmptyLine
	 *
	 * @return string HTML
	 */
	private function composeLines( array $lines, $replaceEmptyLine = true ) {
		$result = [];
		foreach ( $lines as $line ) {
			$line = htmlspecialchars( $line );
			$result[] = $this->replaceEmptyLine( $line, $replaceEmptyLine );
		}
		return implode( "\n", $result );
	}

	/**
	 * Replace empty lines with a no-break space
	 *
	 * @param string $line Lines that should be altered.
	 * @param boolean $replaceEmptyLine
	 *
	 * @return string
	 */
	private function replaceEmptyLine( $line, $replaceEmptyLine = true ) {
		if ( $line === '' && $replaceEmptyLine ) {
			$line = "\u{00A0}";
		}
		return $line;
	}

}
