<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Language;
use OOUI\RadioInputWidget;
use OOUI\ButtonWidget;
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
	 * @var string[]
	 */
	private $sideSelection;

	/**
	 * @param User $user
	 * @param Language $language
	 * @param string[] $sideSelection
	 */
	public function __construct( User $user, Language $language, array $sideSelection ) {
		$this->user = $user;
		$this->language = $language;
		$this->sideSelection = $sideSelection;
	}

	/**
	 * @param array[] $unifiedDiff
	 * @param string[] $yourLines
	 * @param string[] $storedLines
	 *
	 * @return string HTML
	 */
	public function getHtml( array $unifiedDiff, array $yourLines, array $storedLines ) {
		$out = Html::openElement(
			'div', [ 'class' => 'mw-twocolconflict-split-view' ]
		);

		$currRowNum = 0;
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
							$out .= $this->startRow( $currRowNum );
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
		$text = rtrim( $text, "\r\n\u{00A0}" );
		$editorText = rtrim( $rawText, "\r\n" ) . "\n";
		$classes = [ 'mw-twocolconflict-split-editable' ];

		$innerHtml = Html::rawElement(
			'span',
			[ 'class' => 'mw-twocolconflict-split-difftext' ],
			$text
		);
		$innerHtml .= Html::element( 'div', [ 'class' => 'mw-twocolconflict-split-fade' ] );
		$innerHtml .= $this->buildEditButton();
		$innerHtml .= $this->buildSaveButton();
		$innerHtml .= $this->buildResetButton();

		if ( $changeType === 'copy' ) {
			$innerHtml .= $this->buildCollapseButton();
			$innerHtml .= $this->buildExpandButton();
			$classes[] = 'mw-twocolconflict-split-collapsed';
		}

		$innerHtml .= $this->buildResetText( $text, $editorText );
		$innerHtml .= $this->buildTextEditor( $editorText, $rowNum, $changeType );
		$innerHtml .= $this->buildLineFeedField( $rawText, $rowNum, $changeType );

		return Html::rawElement( 'div', [ 'class' => $classes ], $innerHtml );
	}

	private function buildResetText( $text, $editorText ) {
		return Html::rawElement(
				'span', [ 'class' => 'mw-twocolconflict-split-reset-diff-text' ],
				$text
			) . Html::rawElement(
				'span', [ 'class' => 'mw-twocolconflict-split-reset-editor-text' ],
				$editorText
			);
	}

	private function buildTextEditor( $editorText, $rowNum, $changeType ) {
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
			$editorText
		);
	}

	private function buildLineFeedField( $rawText, $rowNum, $changeType ) {
		return Html::hidden(
			"mw-twocolconflict-split-linefeeds[$rowNum][$changeType]",
			$this->countExtraLineFeeds( $rawText )
		);
	}

	private function buildEditButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'edit',
			'title' => wfMessage( 'twocolconflict-split-edit-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-edit-button' ]
		] );
	}

	private function buildSaveButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'check',
			'title' => wfMessage( 'twocolconflict-split-save-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-save-button' ]
		] );
	}

	private function buildResetButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'undo',
			'title' => wfMessage( 'twocolconflict-split-reset-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-reset-button' ]
		] );
	}

	private function buildExpandButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'expand',
			'title' => wfMessage( 'twocolconflict-split-expand-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-expand-button' ]
		] );
	}

	private function buildCollapseButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'collapse',
			'title' => wfMessage( 'twocolconflict-split-collapse-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-collapse-button' ]
		] );
	}

	/**
	 * @param string $text
	 *
	 * @return int
	 */
	private function countExtraLineFeeds( $text ) {
		return substr_count( $text, "\n", strlen( rtrim( $text, "\r\n" ) ) );
	}

	/**
	 * @param int $rowNum
	 *
	 * @return string HTML
	 */
	private function buildSideSelector( $rowNum ) {
		$side = isset( $this->sideSelection[$rowNum] ) ? $this->sideSelection[$rowNum] : '';

		return Html::openElement( 'div', [ 'class' => 'mw-twocolconflict-split-selection' ] ) .
			Html::rawElement( 'div', [], new RadioInputWidget( [
				'name' => 'mw-twocolconflict-side-selector[' . $rowNum . ']',
				'value' => 'other',
				'selected' => $side !== 'your',
			] ) ) .
			Html::rawElement( 'div', [], new RadioInputWidget( [
				'name' => 'mw-twocolconflict-side-selector[' . $rowNum . ']',
				'value' => 'your',
				'selected' => $side === 'your',
			] ) ) .
			Html::closeElement( 'div' );
	}

	/**
	 * Check if a unified diff line contains an edit conflict.
	 *
	 * @param array[] $currentLine
	 *
	 * @return bool
	 */
	private function hasConflictInLine( array $currentLine ) {
		return count( $currentLine ) > 1 &&
			$currentLine[0]['action'] === 'delete' &&
			$currentLine[1]['action'] === 'add';
	}

}
