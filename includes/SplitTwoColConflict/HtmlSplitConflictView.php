<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Language;
use OOUI\RadioInputWidget;
use User;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class HtmlSplitConflictView {

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var Language
	 */
	private $language;

	/**
	 * @param User $user
	 * @param Language $language
	 */
	public function __construct( User $user, Language $language ) {
		$this->user = $user;
		$this->language = $language;
	}

	/**
	 * @param array[][] $unifiedDiff
	 * @param string[] $yourLines
	 * @param string[] $storedLines
	 * @param bool $markAllAsIncomplete
	 *
	 * @return string HTML
	 */
	public function getHtml(
		array $unifiedDiff,
		array $yourLines,
		array $storedLines,
		bool $markAllAsIncomplete
	) : string {
		$out = Html::openElement(
			'div', [ 'class' => 'mw-twocolconflict-split-view' ]
		);

		$currRowNum = 0;
		foreach ( $unifiedDiff as $currentLine ) {
			foreach ( $currentLine as $changeSet ) {
				switch ( $changeSet['action'] ) {
					case 'delete':
						$out .= $this->startRow( $currRowNum, $markAllAsIncomplete );
						$out .= $this->buildRemovedLine(
							$changeSet['old'],
							implode( "\n", array_slice( $storedLines, $changeSet['oldline'], $changeSet['count'] ) ),
							$currRowNum
						);
						$out .= $this->buildSideSelector( $currRowNum );

						if ( !$this->hasConflictInLine( $currentLine ) ) {
							$out .= $this->buildAddedLine( "\u{00A0}", '', $currRowNum );
							$out .= $this->endRow();
							$currRowNum++;
						}
						break;
					case 'add':
						if ( !$this->hasConflictInLine( $currentLine ) ) {
							$out .= $this->startRow( $currRowNum, $markAllAsIncomplete );
							$out .= $this->buildRemovedLine( "\u{00A0}", '', $currRowNum );
							$out .= $this->buildSideSelector( $currRowNum );
						}

						$out .= $this->buildAddedLine(
							$changeSet['new'],
							implode( "\n", array_slice( $yourLines, $changeSet['newline'], $changeSet['count'] ) ),
							$currRowNum
						);
						$out .= $this->endRow();
						$currRowNum++;
						break;
					case 'copy':
						$rawText = implode(
							"\n",
							array_slice( $storedLines, $changeSet['oldline'], $changeSet['count'] )
						);

						$out .= $this->startRow( $currRowNum );
						$out .= $this->buildCopiedLine( $rawText, $currRowNum );
						$out .= $this->endRow();
						$currRowNum++;
						break;
				}
			}
		}

		$out .= Html::closeElement( 'div' );
		return $out;
	}

	private function startRow( int $rowNum, bool $markAsIncomplete = false ) : string {
		$class = 'mw-twocolconflict-split-row';
		if ( $markAsIncomplete ) {
			$class .= ' mw-twocolconflict-no-selection';
		}
		return Html::openElement( 'div', [ 'class' => $class, 'data-line-number' => $rowNum ] );
	}

	private function endRow() : string {
		return Html::closeElement( 'div' );
	}

	private function buildAddedLine( string $diffHtml, string $rawText, int $rowNum ) : string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-add mw-twocolconflict-split-column' ],
			$this->buildEditableTextContainer( $diffHtml, $rawText, $rowNum, 'your' )
		);
	}

	private function buildRemovedLine( string $diffHtml, string $rawText, int $rowNum ) : string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-delete mw-twocolconflict-split-column' ],
			$this->buildEditableTextContainer( $diffHtml, $rawText, $rowNum, 'other' )
		);
	}

	private function buildCopiedLine( string $rawText, int $rowNum ) : string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-copy mw-twocolconflict-split-column' ],
			$this->buildEditableTextContainer( htmlspecialchars( $rawText ), $rawText, $rowNum, 'copy' )
		);
	}

	private function buildSideSelectorLabel() : string {
		return Html::openElement(
			'div', [ 'class' => 'mw-twocolconflict-split-selector-label' ]
		) .
		Html::element(
			'span',
			[],
			wfMessage( 'twocolconflict-split-choose-version' )->text()
		) .
		Html::closeElement( 'div' );
	}

	private function buildEditableTextContainer(
		string $diffHtml,
		string $rawText,
		int $rowNum,
		string $changeType
	) : string {
		return ( new HtmlEditableTextComponent(
			$this->user, $this->language
		) )->getHtml(
			$diffHtml, $rawText, $rowNum, $changeType
		);
	}

	/**
	 * @param int $rowNum Identifier for this line.
	 *
	 * @return string HTML
	 */
	private function buildSideSelector( int $rowNum ) : string {
		return Html::openElement( 'div' ) .
			$this->buildSideSelectorLabel() .
			Html::openElement( 'div', [ 'class' => 'mw-twocolconflict-split-selection' ] ) .
			Html::rawElement( 'div', [], new RadioInputWidget( [
				'name' => 'mw-twocolconflict-side-selector[' . $rowNum . ']',
				'value' => 'other',
				'tabIndex' => '1',
			] ) ) .
			Html::rawElement( 'div', [], new RadioInputWidget( [
				'name' => 'mw-twocolconflict-side-selector[' . $rowNum . ']',
				'value' => 'your',
				'selected' => true,
				'tabIndex' => '1',
			] ) ) .
			Html::closeElement( 'div' ) .
			Html::closeElement( 'div' );
	}

	/**
	 * Check if a unified diff line contains an edit conflict.
	 *
	 * @param array[] $currentLine
	 *
	 * @return bool
	 */
	private function hasConflictInLine( array $currentLine ) : bool {
		return count( $currentLine ) > 1 &&
			$currentLine[0]['action'] === 'delete' &&
			$currentLine[1]['action'] === 'add';
	}

}
