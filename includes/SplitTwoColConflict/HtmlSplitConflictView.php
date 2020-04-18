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
	 * @var HtmlEditableTextComponent
	 */
	private $editableTextComponent;

	/**
	 * @param User $user
	 * @param Language $language
	 */
	public function __construct( User $user, Language $language ) {
		$this->editableTextComponent = new HtmlEditableTextComponent( $user, $language );
	}

	/**
	 * @param array[] $unifiedDiff
	 * @param bool $markAllAsIncomplete
	 *
	 * @return string HTML
	 */
	public function getHtml(
		array $unifiedDiff,
		bool $markAllAsIncomplete
	) : string {
		$out = '';

		foreach ( $unifiedDiff as $currRowNum => $changeSet ) {
			if ( $changeSet['action'] === 'copy' ) {
				// Copy block across both columns.
				$line = $this->buildCopiedLine( $changeSet['copytext'], $currRowNum );
				$markAsIncomplete = false;
			} else {
				// Old and new split across two columns.
				$line = $this->buildRemovedLine(
						$changeSet['oldhtml'],
						$changeSet['oldtext'],
						$currRowNum
					) .
					$this->buildSideSelector( $currRowNum ) .
					$this->buildAddedLine(
						$changeSet['newhtml'],
						$changeSet['newtext'],
						$currRowNum
					);
				$markAsIncomplete = $markAllAsIncomplete;
			}

			$out .= Html::rawElement(
				'div',
				[
					'class' => 'mw-twocolconflict-split-row' .
						( $markAsIncomplete ? ' mw-twocolconflict-no-selection' : '' ),
					'data-line-number' => $currRowNum,
				],
				$line
			);
		}

		return Html::rawElement( 'div', [ 'class' => 'mw-twocolconflict-split-view' ], $out );
	}

	private function buildAddedLine( string $diffHtml, string $text, int $rowNum ) : string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-add mw-twocolconflict-split-column' ],
			$this->buildEditableTextContainer( $diffHtml, $text, $rowNum, 'your' )
		);
	}

	private function buildRemovedLine( string $diffHtml, string $rawText, int $rowNum ) : string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-delete mw-twocolconflict-split-column' ],
			$this->buildEditableTextContainer( $diffHtml, $rawText, $rowNum, 'other' )
		);
	}

	private function buildCopiedLine( string $text, int $rowNum ) : string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-copy mw-twocolconflict-split-column' ],
			$this->buildEditableTextContainer( htmlspecialchars( $text ), $text, $rowNum, 'copy' )
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
		string $text,
		int $rowNum,
		string $changeType
	) : string {
		return $this->editableTextComponent->getHtml(
			$diffHtml, $text, $rowNum, $changeType
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

}
