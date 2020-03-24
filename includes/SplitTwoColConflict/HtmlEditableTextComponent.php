<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Language;
use OOUI\ButtonWidget;
use User;

// TODO: Should this be an official OOUI component?
class HtmlEditableTextComponent {
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
	 * @param string $diffHtml
	 * @param string $text
	 * @param int $rowNum
	 * @param string $changeType
	 * @param bool $isDisabled
	 * @return string
	 */
	public function getHtml(
		string $diffHtml,
		string $text,
		int $rowNum,
		string $changeType,
		bool $isDisabled = false
	) : string {
		$diffHtml = rtrim( $diffHtml, "\r\n\u{00A0}" );
		$editorText = rtrim( $text, "\r\n" ) . "\n";
		$classes = [ 'mw-twocolconflict-split-editable' ];

		$innerHtml = Html::rawElement(
			'span',
			[ 'class' => 'mw-twocolconflict-split-difftext' ],
			$diffHtml
		);
		$innerHtml .= Html::element( 'div', [ 'class' => 'mw-twocolconflict-split-fade' ] );
		$innerHtml .= $this->buildTextEditor( $editorText, $rowNum, $changeType, $isDisabled );
		if ( !$isDisabled ) {
			$innerHtml .= $this->buildEditButton();
			$innerHtml .= $this->buildSaveButton();
			$innerHtml .= $this->buildResetButton();
		}

		if ( $changeType === 'copy' ) {
			$innerHtml .= $this->buildExpandButton();
			$innerHtml .= $this->buildCollapseButton();
			$classes[] = 'mw-twocolconflict-split-collapsed';
		}

		$innerHtml .= $this->buildResetText( $diffHtml, $editorText );
		$innerHtml .= $this->buildLineFeedField( $text, $rowNum, $changeType );

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

	private function buildTextEditor(
		string $editorText,
		int $rowNum,
		string $changeType,
		bool $isDisabled
	) : string {
		$class = 'mw-editfont-' . $this->user->getOption( 'editfont' );
		$attributes = [
			'class' => $class . ' mw-twocolconflict-split-editor',
			'name' => 'mw-twocolconflict-split-content[' . $rowNum . '][' . $changeType . ']',
			'lang' => $this->language->getHtmlCode(),
			'dir' => $this->language->getDir(),
			'rows' => $this->rowsForText( $editorText ),
			'autocomplete' => 'off',
			'tabindex' => '1',
		];
		if ( $isDisabled ) {
			$attributes['readonly'] = 'readonly';
		}

		return Html::element( 'textarea', $attributes, $editorText );
	}

	private function buildLineFeedField( string $text, int $rowNum, string $changeType ) : string {
		return Html::hidden(
			"mw-twocolconflict-split-linefeeds[$rowNum][$changeType]",
			$this->countExtraLineFeeds( $text )
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
