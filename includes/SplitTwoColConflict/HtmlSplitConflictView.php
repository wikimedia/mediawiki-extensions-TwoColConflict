<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Language;
use MediaWiki\EditPage\TextboxBuilder;
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
	 * @param array[] $unifiedDiff
	 * @param string[] $yourLines
	 * @param string[] $storedLines
	 * @return string
	 */
	public function getHtml( array $unifiedDiff, array $yourLines, array $storedLines ) {
		$out = Html::openElement(
			'div', [ 'class' => 'mw-twocolconflict-split-view' ]
		);

		$currRowNum = 1;
		$isFirstNonCopyLine = true;
		foreach ( $unifiedDiff as $key => $currentLine ) {
			foreach ( $currentLine as $changeSet ) {
				if ( $changeSet['action'] !== 'copy' && $isFirstNonCopyLine ) {
					$out .= $this->buildSideSelectorLabel();
					$isFirstNonCopyLine = false;
				}
				switch ( $changeSet['action'] ) {
					case 'delete':
						$out .= $this->startRow( $currRowNum );
						$out .= $this->buildRemovedLine(
							$changeSet['old'],
							$storedLines[ $changeSet['oldline'] - 1 ],
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
							$out .= $this->startRow( $currRowNum );
							$out .= $this->buildRemovedLine( "\u{00A0}", '', $currRowNum );
							$out .= $this->buildSideSelector( $currRowNum );
						}

						$out .= $this->buildAddedLine(
							$changeSet['new'],
							$yourLines[ $changeSet['newline'] - 1 ],
							$currRowNum
						);
						$out .= $this->endRow();
						$currRowNum++;
						break;
					case 'copy':
						$out .= $this->startRow( $currRowNum );
						$out .= $this->buildCopiedLine( $changeSet['copy'], $currRowNum );
						$out .= $this->endRow();
						$currRowNum++;
						break;
				}
			}
		}

		$out .= Html::closeElement( 'div' );
		return $out;
	}

	private function startRow( $rowNum ) {
		return Html::openElement(
			'div', [ 'class' => 'mw-twocolconflict-split-row', 'data-line-number' => $rowNum ]
		);
	}

	private function endRow() {
		return Html::closeElement( 'div' );
	}

	private function buildAddedLine( $text, $rawText, $rowNum ) {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-add mw-twocolconflict-split-column' ],
			$this->buildEditableTextContainer( $text, $rawText, $rowNum, 'your' )
		);
	}

	private function buildRemovedLine( $text, $rawText, $rowNum ) {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-delete mw-twocolconflict-split-column' ],
			$this->buildEditableTextContainer( $text, $rawText, $rowNum, 'other' )
		);
	}

	private function buildCopiedLine( $text, $rowNum ) {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-copy mw-twocolconflict-split-column' ],
			$this->buildEditableTextContainer( $text, $text, $rowNum, 'copy' )
		);
	}

	private function buildSideSelectorLabel() {
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

	private function buildEditableTextContainer( $text, $rawText, $rowNum, $changeType ) {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-editable' ],
			Html::rawElement(
				'span',
				[ 'class' => 'mw-twocolconflict-split-difftext' ],
				$text
			) .
			$this->buildTextEditor( $rawText, $rowNum, $changeType )
		);
	}

	private function buildTextEditor( $text, $rowNum, $changeType ) {
		$class = 'mw-editfont-' . $this->user->getOption( 'editfont' );

		return Html::rawElement(
			'textarea',
			[
				'class' => $class . ' mw-twocolconflict-split-editor',
				'name' => 'mw-twocolconflict-split-content[' . $rowNum . '][' . $changeType . ']',
				'lang' => $this->language->getHtmlCode(),
				'dir' => $this->language->getDir(),
				'rows' => '6',
				'autocomplete' => 'off',
			],
			( new TextboxBuilder() )->addNewLineAtEnd( $text )
		);
	}

	private function buildSideSelector( $rowNum ) {
		return Html::openElement( 'div', [ 'class' => 'mw-twocolconflict-split-selection' ] ) .
			Html::rawElement( 'div', [], new RadioInputWidget( [
				'name' => 'mw-twocolconflict-side-selector[' . $rowNum . ']',
				'value' => 'other',
				'autocomplete' => 'off',
				'selected' => true,
			] ) ) .
			Html::rawElement( 'div', [], new RadioInputWidget( [
				'name' => 'mw-twocolconflict-side-selector[' . $rowNum . ']',
				'value' => 'your',
				'autocomplete' => 'off',
			] ) ) .
			Html::closeElement( 'div' );
	}

	/**
	 * Check if a unified diff line contains an edit conflict.
	 *
	 * @param array[] $currentLine
	 * @return bool
	 */
	private function hasConflictInLine( array $currentLine ) {
		return count( $currentLine ) > 1 &&
			$currentLine[0]['action'] === 'delete' &&
			$currentLine[1]['action'] === 'add';
	}

}
