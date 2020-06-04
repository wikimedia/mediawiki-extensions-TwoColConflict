<?php

namespace TwoColConflict;

use Html;
use MessageLocalizer;
use OOUI\RadioInputWidget;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class HtmlSideSelectorComponent {

	/**
	 * @var MessageLocalizer
	 */
	private $messageLocalizer;

	/**
	 * @param MessageLocalizer $messageLocalizer
	 */
	public function __construct( MessageLocalizer $messageLocalizer ) {
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @return string HTML
	 */
	public function getHeaderHtml() : string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-selection-container' ],
			$this->buildSideSelectorLabel( 'twocolconflict-split-select-all' ) .
			Html::rawElement(
				'div',
				[ 'class' => [
					'mw-twocolconflict-split-selection',
					'mw-twocolconflict-split-selection-header',
				] ],
				Html::rawElement( 'div', [], new RadioInputWidget( [
					'name' => 'mw-twocolconflict-side-selector',
					'value' => 'other',
					'tabIndex' => '1',
				] ) ) .
				Html::rawElement( 'div', [], new RadioInputWidget( [
					'name' => 'mw-twocolconflict-side-selector',
					'value' => 'your',
					'tabIndex' => '1',
				] ) )
			)
		);
	}

	/**
	 * @param int $rowNum Identifier for this line.
	 *
	 * @return string HTML
	 */
	public function getRowHtml( int $rowNum ) : string {
		return Html::rawElement(
			'div',
			// Note: This CSS class is currently unused
			[ 'class' => 'mw-twocolconflict-split-selection-container' ],
			$this->buildSideSelectorLabel( 'twocolconflict-split-choose-version' ) .
			Html::rawElement(
				'div',
				[ 'class' => [
					'mw-twocolconflict-split-selection',
					'mw-twocolconflict-split-selection-row'
				] ],
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
				] ) )
			)
		);
	}

	/**
	 * @param string $msg
	 *
	 * @return string HTML
	 */
	private function buildSideSelectorLabel( string $msg ) : string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-selector-label' ],
			Html::element(
				'span',
				[],
				$this->messageLocalizer->msg( $msg )->text()
			)
		);
	}

}