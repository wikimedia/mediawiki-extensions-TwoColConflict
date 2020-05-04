<?php

namespace TwoColConflict;

use Html;
use MessageLocalizer;
use OOUI\HtmlSnippet;
use OOUI\IconWidget;
use OOUI\MessageWidget;

class CoreUiHintHtml {

	/**
	 * @var MessageLocalizer
	 */
	private $messageLocalizer;

	/**
	 * @param MessageLocalizer $messageLocalizer
	 */
	public function __construct(
		MessageLocalizer $messageLocalizer
	) {
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @return string
	 */
	public function getHtml() : string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-core-ui-hint' ],
			Html::check(
				'mw-twocolconflict-disable-core-hint',
				false,
				[ 'id' => 'mw-twocolconflict-disable-core-hint' ]
			) .
			new MessageWidget( [
				'label' => new HtmlSnippet(
					$this->messageLocalizer->msg( 'twocolconflict-core-ui-hint' )->parse() .
					Html::rawElement(
						'label',
						[ 'for' => 'mw-twocolconflict-disable-core-hint' ],
						new IconWidget( [ 'icon' => 'close' ] )
					)
				)
			] )
		);
	}

}
