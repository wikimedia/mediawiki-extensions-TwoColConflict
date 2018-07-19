<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;

class HtmlSplitConflictView {

	/**
	 * @param array $unifiedDiff
	 * @return string
	 */
	public function getHtml( $unifiedDiff ) {
		$out = Html::openElement(
			'div', [ 'class' => 'mw-twocolconflict-split-view' ]
		);

		$currRowNum = 1;
		foreach ( $unifiedDiff as $key => $currentLine ) {
				foreach ( $currentLine as $changeSet ) {
					switch ( $changeSet['action'] ) {
						case 'delete':
							$out .= $this->startRow( $currRowNum );
							$out .= $this->buildRemovedLine( $changeSet['old'] );

							if ( !$this->hasConflictInLine( $currentLine ) ) {
								$out .= $this->buildAddedLine( "\u{00A0}" );
								$out .= $this->endRow();
								$currRowNum++;
							}
							break;
						case 'add':
							if ( !$this->hasConflictInLine( $currentLine ) ) {
								$out .= $this->startRow( $currRowNum );
								$out .= $this->buildRemovedLine( "\u{00A0}" );
							}

							$out .= $this->buildAddedLine( $changeSet['new'] );
							$out .= $this->endRow();
							$currRowNum++;
							break;
						case 'copy':
							$out .= $this->startRow( $currRowNum );
							$out .= $this->buildCopiedLine( $changeSet['copy'] );
							$out .= $this->endRow();
							$currRowNum++;
							break;
					}
				}
		}

		$out .= Html::closeElement( 'div' );
		return $out;
	}

	private function startRow( $lineNum ) {
		return Html::openElement(
			'div', [ 'class' => 'mw-twocolconflict-split-row', 'data-line-number' => $lineNum ]
		);
	}

	private function endRow() {
		return Html::closeElement( 'div' );
	}

	private function buildAddedLine( $text ) {
		return Html::rawElement(
			'div', 	[ 'class' => 'mw-twocolconflict-split-add mw-twocolconflict-split-column' ],
			$text
		);
	}

	private function buildRemovedLine( $text ) {
		return Html::rawElement(
			'div', [ 'class' => 'mw-twocolconflict-split-delete mw-twocolconflict-split-column' ],
			$text
		);
	}

	private function buildCopiedLine( $text ) {
		return Html::rawElement(
			'div', [ 'class' => 'mw-twocolconflict-split-copy mw-twocolconflict-split-column' ],
			$text
		);
	}

	/**
	 * Check if a unified diff line contains an edit conflict.
	 *
	 * @param array[] $currentLine
	 * @return boolean
	 */
	private function hasConflictInLine( array $currentLine ) {
		if ( count( $currentLine ) < 2 ) {
			return false;
		}
		return $currentLine[0]['action'] === 'delete' &&
			$currentLine[1]['action'] === 'add';
	}

}
