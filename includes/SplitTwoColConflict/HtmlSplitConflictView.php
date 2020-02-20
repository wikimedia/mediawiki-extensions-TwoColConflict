<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Language;
use OOUI\ButtonWidget;
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
	 *
	 * @return string HTML
	 */
	public function getHtml( array $unifiedDiff, array $yourLines, array $storedLines ) : string {
		$out = Html::openElement(
			'div', [ 'class' => 'mw-twocolconflict-split-view' ]
		);

		$currRowNum = 0;
		$isFirstNonCopyLine = true;
		foreach ( $unifiedDiff as $currentLine ) {
			foreach ( $currentLine as $changeSet ) {
				switch ( $changeSet['action'] ) {
					case 'delete':
						$out .= $this->startRow( $currRowNum );
						$out .= $this->buildRemovedLine(
							$changeSet['old'],
							implode( "\n", array_slice( $storedLines, $changeSet['oldline'], $changeSet['count'] ) ),
							$currRowNum
						);
						$out .= $this->buildSideSelector( $currRowNum, $isFirstNonCopyLine );

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
							$out .= $this->buildSideSelector( $currRowNum, $isFirstNonCopyLine );
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

	private function startRow( int $rowNum ) : string {
		return Html::openElement(
			'div', [ 'class' => 'mw-twocolconflict-split-row', 'data-line-number' => $rowNum ]
		);
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
		$diffHtml = rtrim( $diffHtml, "\r\n\u{00A0}" );
		$editorText = rtrim( $rawText, "\r\n" ) . "\n";
		$classes = [ 'mw-twocolconflict-split-editable' ];

		$innerHtml = Html::rawElement(
			'span',
			[ 'class' => 'mw-twocolconflict-split-difftext' ],
			$diffHtml
		);
		$innerHtml .= Html::element( 'div', [ 'class' => 'mw-twocolconflict-split-fade' ] );
		$innerHtml .= $this->buildTextEditor( $editorText, $rowNum, $changeType );
		$innerHtml .= $this->buildEditButton();
		$innerHtml .= $this->buildSaveButton();
		$innerHtml .= $this->buildResetButton();

		if ( $changeType === 'copy' ) {
			$innerHtml .= $this->buildExpandButton();
			$innerHtml .= $this->buildCollapseButton();
			$classes[] = 'mw-twocolconflict-split-collapsed';
		}

		$innerHtml .= $this->buildResetText( $diffHtml, $editorText );
		$innerHtml .= $this->buildLineFeedField( $rawText, $rowNum, $changeType );

		return Html::rawElement( 'div', [ 'class' => $classes ], $innerHtml );
	}

	private function buildResetText( string $diffHtml, string $editorText ) : string {
		return Html::rawElement(
				'span', [ 'class' => 'mw-twocolconflict-split-reset-diff-text' ],
				$diffHtml
			) . Html::element(
				'span', [ 'class' => 'mw-twocolconflict-split-reset-editor-text' ],
				$editorText
			);
	}

	private function buildTextEditor( string $editorText, int $rowNum, string $changeType ) : string {
		$class = 'mw-editfont-' . $this->user->getOption( 'editfont' );

		return Html::element(
			'textarea',
			[
				'class' => $class . ' mw-twocolconflict-split-editor',
				'name' => 'mw-twocolconflict-split-content[' . $rowNum . '][' . $changeType . ']',
				'lang' => $this->language->getHtmlCode(),
				'dir' => $this->language->getDir(),
				'rows' => $this->rowsForText( $editorText ),
				'autocomplete' => 'off',
				'tabindex' => '1',
			],
			$editorText
		);
	}

	private function buildLineFeedField( string $rawText, int $rowNum, string $changeType ) : string {
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
			'classes' => [ 'mw-twocolconflict-split-edit-button' ],
			'tabIndex' => '1',
		] );
	}

	private function buildSaveButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'check',
			'title' => wfMessage( 'twocolconflict-split-save-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-save-button' ],
			'tabIndex' => '1',
		] );
	}

	private function buildResetButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'close',
			'title' => wfMessage( 'twocolconflict-split-reset-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-reset-button' ],
			'tabIndex' => '1',
		] );
	}

	private function buildExpandButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'expand',
			'title' => wfMessage( 'twocolconflict-split-expand-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-expand-button' ],
			'tabIndex' => '1',
		] );
	}

	private function buildCollapseButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'collapse',
			'title' => wfMessage( 'twocolconflict-split-collapse-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-collapse-button' ],
			'tabIndex' => '1',
		] );
	}

	/**
	 * @param string $text
	 *
	 * @return int
	 */
	private function countExtraLineFeeds( string $text ) : int {
		return substr_count( $text, "\n", strlen( rtrim( $text, "\r\n" ) ) );
	}

	/**
	 * @param int $rowNum Identifier for this line.
	 * @param bool &$isFirstNonCopyLine If true, then show a legend above the selector.
	 *
	 * @return string HTML
	 */
	private function buildSideSelector( int $rowNum, bool &$isFirstNonCopyLine ) : string {
		$label = $isFirstNonCopyLine ? $this->buildSideSelectorLabel() : '';
		$isFirstNonCopyLine = false;

		return Html::openElement( 'div' ) .
			$label .
			Html::openElement( 'div', [ 'class' => 'mw-twocolconflict-split-selection' ] ) .
			Html::rawElement( 'div', [], new RadioInputWidget( [
				'name' => 'mw-twocolconflict-side-selector[' . $rowNum . ']',
				'value' => 'other',
				'selected' => true,
				'tabIndex' => '1',
			] ) ) .
			Html::rawElement( 'div', [], new RadioInputWidget( [
				'name' => 'mw-twocolconflict-side-selector[' . $rowNum . ']',
				'value' => 'your',
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

	/**
	 * Estimate the appropriate size textbox to use for a given text.
	 * @param string $text Contents of the textbox
	 * @return int Suggested number of rows
	 */
	private function rowsForText( string $text ) : int {
		$thresholds = [
			80 * 10 => 18,
			80 * 4 => 6,
			0 => 3,
		];
		$numChars = function_exists( 'grapheme_strlen' )
			? grapheme_strlen( $text ) : mb_strlen( $text );
		$numLines = substr_count( $text, "\n" ) + 1;
		foreach ( $thresholds as $minChars => $rows ) {
			if ( $numChars >= $minChars ) {
				return max( $rows, $numLines );
			}
		}
		// Should be unreachable.
		return $numLines;
	}

}
