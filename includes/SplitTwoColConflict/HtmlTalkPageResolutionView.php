<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Language;
use OOUI\IconWidget;
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
		$out = Html::element( 'div', [ 'class' => 'mw-twocolconflict-talk-header' ] );
		$out .= Html::hidden(
			'mw-twocolconflict-resolution-first-entry',
			'other'
		);

		foreach ( $unifiedDiff as $currRowNum => $changeSet ) {
			$text = $changeSet['copytext'] ?? $changeSet['newtext'];

			switch ( $currRowNum ) {
				case $otherIndex:
					// TODO wrapping these while using a loop is a bit ugly ideally $unifiedDiff
					// has a different structure
					$out .= Html::openElement(
						'div',
						[ 'class' => 'mw-twocolconflict-suggestion-draggable' ]
					);
					$out .= $this->buildDraggableRow(
						$text,
						$currRowNum,
						'delete',
						'other',
						true,
						'twocolconflict-talk-conflicting'
					);
					break;
				case $yourIndex:
					$out .= $this->buildDraggableRow(
						$text,
						$currRowNum,
						'add',
						'your',
						false,
						'twocolconflict-talk-your'
					);
					$out .= Html::closeElement( 'div' );
					break;
				default:
					$out .= $this->buildCopyRow( $text, $currRowNum );
			}
		}

		$out .= Html::hidden( 'mw-twocolconflict-single-column-view', true );

		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-view mw-twocolconflict-single-column-view' ],
			$out
		);
	}

	private function startRow( int $rowNum, $isDraggable = false ) : string {
		$class = 'mw-twocolconflict-single-row';
		if ( $isDraggable ) {
			$class .= ' mw-twocolconflict-draggable';
		}
		return Html::openElement( 'div', [ 'class' => $class, 'data-line-number' => $rowNum ] );
	}

	private function endRow() : string {
		return Html::closeElement( 'div' );
	}

	private function buildDraggableRow(
		string $rawText,
		int $rowNum,
		string $classSuffix,
		string $changeType,
		bool $isDisabled,
		string $draggableLabel
	) : string {
		$out = $this->startRow( $rowNum, true );
		$out .= Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-draggable-handle' ],
			new IconWidget( [
				'icon' => 'draggable',
			] )
		);
		$out .= Html::element(
			'div',
			[ 'class' => 'mw-twocolconflict-draggable-label' ],
			wfMessage( $draggableLabel )->text()
		);
		$out .= Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-' . $classSuffix . ' mw-twocolconflict-single-column' ],
			( new HtmlEditableTextComponent( $this->user, $this->language ) )->getHtml(
				htmlspecialchars( $rawText ), $rawText, $rowNum, $changeType, $isDisabled )
		);
		$out .= $this->endRow();
		return $out;
	}

	private function buildCopyRow(
		string $rawText,
		int $rowNum
	) : string {
		$out = $this->startRow( $rowNum );
		$out .= Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-copy mw-twocolconflict-single-column' ],
			( new HtmlEditableTextComponent( $this->user, $this->language ) )->getHtml(
				htmlspecialchars( $rawText ), $rawText, $rowNum, 'copy', true )
		);
		$out .= $this->endRow();
		return $out;
	}

}
