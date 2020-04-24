<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use MessageLocalizer;
use OOUI\IconWidget;

/**
 * TODO: Clean up, maybe CSS class names should match change type, and "split" replaced with
 *  "single" where appropriate.
 */
class HtmlTalkPageResolutionView {

	/**
	 * @var HtmlEditableTextComponent
	 */
	private $editableTextComponent;

	/**
	 * @var MessageLocalizer
	 */
	private $messageLocalizer;

	/**
	 * @param HtmlEditableTextComponent $editableTextComponent
	 * @param MessageLocalizer $messageLocalizer
	 */
	public function __construct(
		HtmlEditableTextComponent $editableTextComponent,
		MessageLocalizer $messageLocalizer
	) {
		$this->editableTextComponent = $editableTextComponent;
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @param array[] $unifiedDiff
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

	private function wrapRow( string $html, $isDraggable = false ) : string {
		return Html::rawElement(
			'div',
			[
				'class' => 'mw-twocolconflict-single-row' .
					( $isDraggable ? ' mw-twocolconflict-draggable' : '' )
			],
			$html
		);
	}

	private function buildDraggableRow(
		string $rawText,
		int $rowNum,
		string $classSuffix,
		string $changeType,
		bool $isDisabled,
		string $draggableLabel
	) : string {
		$out = Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-draggable-handle' ],
			new IconWidget( [
				'icon' => 'draggable',
			] )
		);

		$out .= Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-draggable-label' ],
			Html::rawElement(
				'span',
				[],
				Html::element(
					'span',
					[ 'class' => 'mw-twocolconflict-split-' . $classSuffix ],
					$this->messageLocalizer->msg( $draggableLabel )->text()
				)
			)
		);

		$out .= Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-' . $classSuffix . ' mw-twocolconflict-single-column' ],
			$this->editableTextComponent->getHtml(
				htmlspecialchars( $rawText ), $rawText, $rowNum, $changeType, $isDisabled )
		);
		return $this->wrapRow( $out, true );
	}

	private function buildCopyRow(
		string $rawText,
		int $rowNum
	) : string {
		$out = Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-copy mw-twocolconflict-single-column' ],
			$this->editableTextComponent->getHtml(
				htmlspecialchars( $rawText ), $rawText, $rowNum, 'copy', true )
		);
		return $this->wrapRow( $out );
	}

}
