<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use MessageLocalizer;
use OOUI\ButtonWidget;
use OOUI\FieldLayout;
use OOUI\FieldsetLayout;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;
use OOUI\RadioInputWidget;

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
	 * @param bool $isBetaFeature
	 *
	 * @return string HTML
	 */
	public function getHtml(
		array $unifiedDiff,
		int $otherIndex,
		int $yourIndex,
		bool $isBetaFeature
	) : string {
		$out = $this->getMessageBox(
			'twocolconflict-talk-header-overview', 'error', 'mw-twocolconflict-overview' );
		$hintMsg = $isBetaFeature ?
			'twocolconflict-split-header-hint-beta' : 'twocolconflict-split-header-hint';
		$out .= $this->getMessageBox( $hintMsg, 'notice' );

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

					$out .= Html::rawElement(
						'div',
						[ 'class' => 'mw-twocolconflict-single-swap-button-container' ],
						new ButtonWidget( [
							'infusable' => true,
							'framed' => false,
							'icon' => 'markup',
							'classes' => [ 'mw-twocolconflict-single-swap-button' ],
							'tabIndex' => '1'
						] )
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

		$out .= $this->buildOrderSelector() .
			Html::hidden( 'mw-twocolconflict-single-column-view', true );

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

	private function buildOrderSelector() {
		$out = new FieldsetLayout( [
			'label' => $this->messageLocalizer->msg( 'twocolconflict-talk-reorder-prompt' )->text(),
			'items' => [
				new FieldLayout(
					new RadioInputWidget( [
						'name' => 'mw-twocolconflict-reorder',
						'value' => 'reverse',
						'tabIndex' => '1',
					] ),
					[
						'align' => 'inline',
						'label' => $this->messageLocalizer->msg( 'twocolconflict-talk-reverse-order' )->text(),
					]
				),
				new FieldLayout(
					new RadioInputWidget( [
						'name' => 'mw-twocolconflict-reorder',
						'value' => 'no-change',
						'selected' => true,
						'tabIndex' => '1',
					] ),
					[
						'align' => 'inline',
						'label' => $this->messageLocalizer->msg( 'twocolconflict-talk-same-order' )->text(),
					]
				),
			],
		] );

		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-order-selector' ],
			$out
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

	private function getMessageBox( string $messageKey, string $type, $classes = [] ) : string {
		$html = $this->messageLocalizer->msg( $messageKey )->parse();
		// Force feedback links to be opened in a new tab, and not lose the edit
		$html = preg_replace( '/<a\b(?![^<>]*\starget=)/', '<a target="_blank"', $html );
		return ( new MessageWidget( [
			'label' => new HtmlSnippet( $html ),
			'type' => $type,
		] ) )
			->addClasses( array_merge( [ 'mw-twocolconflict-messageWidget' ], (array)$classes ) )
			->toString();
	}

}
