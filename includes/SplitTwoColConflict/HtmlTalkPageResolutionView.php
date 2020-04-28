<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use MessageLocalizer;

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
					$out .= $this->buildConflictingTalkRow(
						$text,
						$currRowNum,
						'delete',
						'other',
						true,
						'twocolconflict-talk-conflicting'
					);
					break;
				case $yourIndex:
					$out .= $this->buildConflictingTalkRow(
						$text,
						$currRowNum,
						'add',
						'your',
						false,
						'twocolconflict-talk-your'
					);
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

	private function wrapRow( string $html, $isConflicting = false ) : string {
		return Html::rawElement(
			'div',
			[
				'class' => 'mw-twocolconflict-single-row' .
					( $isConflicting ? ' mw-twocolconflict-conflicting-talk-row' : '' )
			],
			$html
		);
	}

	private function buildConflictingTalkRow(
		string $rawText,
		int $rowNum,
		string $classSuffix,
		string $changeType,
		bool $isDisabled,
		string $conflictingTalkLabel
	) : string {
		$out = Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-conflicting-talk-label' ],
			Html::rawElement(
				'span',
				[],
				Html::element(
					'span',
					[ 'class' => 'mw-twocolconflict-split-' . $classSuffix ],
					$this->messageLocalizer->msg( $conflictingTalkLabel )->text()
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
