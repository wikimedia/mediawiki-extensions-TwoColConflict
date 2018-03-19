<?php
use MediaWiki\Diff\WordAccumulator;

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
	private $oldline = 1;

	/**
	 * @var int
	 */
	private $newline = 1;

	/**
	 * @var array[]
	 */
	private $retval = [];

	/**
	 * @param Diff $diff A Diff object.
	 *
	 * @return array[] Associative array showing lists of changes in lines of the original text.
	 */
	public function format( $diff ) {
		$this->oldline = 1;
		$this->newline = 1;
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
					$wordLevelDiff = new WordLevelDiff( $edit->getOrig(), $edit->getClosing() );

					$this->retval[$this->oldline][] = [
						'action' => 'delete',
						'old' => $this->getOriginalInlineDiff( $wordLevelDiff ),
						'oldline' => $this->oldline,
					];
					$this->retval[$this->oldline][] = [
						'action' => 'add',
						'new' => $this->getClosingInlineDiff( $wordLevelDiff ),
						'newline' => $this->newline
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
	 * @param string[] $lines Lines that should be marked deleted.
	 */
	private function deleteLines( array $lines ) {
		$this->retval[$this->oldline][] = [
			'action' => 'delete',
			'old' => "<del{$this->delClass}>" . $this->composeLines( $lines ) . '</del>',
			'oldline' => $this->oldline,
		];
	}

	/**
	 * @param string[] $lines Lines that should be marked as added.
	 */
	private function addLines( array $lines ) {
		$this->retval[$this->oldline][] = [
			'action' => 'add',
			'new' => "<ins{$this->insClass}>" . $this->composeLines( $lines ) . '</ins>',
			'newline' => $this->newline
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
			if ( $edit->type == 'copy' ) {
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
			if ( $edit->type == 'copy' ) {
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
	 * @return string
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
	 * Replace empty lines with a NBSP
	 *
	 * @param string $line Lines that should be altered.
	 * @param boolean $replaceEmptyLine
	 *
	 * @return string
	 */
	private function replaceEmptyLine( $line, $replaceEmptyLine = true ) {
		if ( $line === '' && $replaceEmptyLine ) {
			$line = '&#160;';
		}
		return $line;
	}
}
