<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Language;
use User;

/**
 * TODO: Clean up, maybe CSS class names should match change type, and "split" replaced with
 *  "single" where appropriate.
 */
class HtmlTalkPageResolutionView {
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
	 * @param array $unifiedDiff
	 * @param int $otherIndex
	 * @param int $yourIndex
	 *
	 * @return string HTML
	 */
	public function getHtml(
		array $unifiedDiff,
		int $otherIndex,
		int $yourIndex
	) : string {
		$out = Html::openElement(
			'div', [ 'class' => 'mw-twocolconflict-split-view' ]
		);

		foreach ( $unifiedDiff as $currRowNum => $changeSet ) {
			$text = $changeSet['copytext'] ?? $changeSet['newtext'];

			switch ( $currRowNum ) {
				case $otherIndex:
					$out .= $this->buildRow( $text, $currRowNum, 'delete', 'other', true );
					break;
				case $yourIndex:
					$out .= $this->buildRow( $text, $currRowNum, 'add', 'your', false );
					break;
				default:
					$out .= $this->buildRow( $text, $currRowNum, 'copy', 'copy', true );
			}
		}

		$out .= Html::closeElement( 'div' );
		$out .= Html::hidden( 'mw-twocolconflict-single-column-view', true );

		return $out;
	}

	private function startRow( int $rowNum ) : string {
		$class = 'mw-twocolconflict-single-row';
		return Html::openElement( 'div', [ 'class' => $class, 'data-line-number' => $rowNum ] );
	}

	private function endRow() : string {
		return Html::closeElement( 'div' );
	}

	private function buildRow(
		string $rawText,
		int $rowNum,
		string $classSuffix,
		string $changeType,
		bool $isDisabled
	) : string {
		return $this->startRow( $rowNum ) .
			Html::rawElement(
				'div',
				[ 'class' => 'mw-twocolconflict-split-' . $classSuffix . ' mw-twocolconflict-single-column' ],
				( new HtmlEditableTextComponent( $this->user, $this->language ) )->getHtml(
					htmlspecialchars( $rawText ), $rawText, $rowNum, $changeType, $isDisabled )
			) .
			$this->endRow();
	}

}
