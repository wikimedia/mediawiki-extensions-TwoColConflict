<?php

use OOUI\ButtonInputWidget;

/**
 * Form allowing the user to change the base text for the conflict.
 */
class HtmlSpecialTestTextForm {

	/**
	 * @var SpecialPage
	 */
	private $specialPage;

	/**
	 * @param SpecialPage $specialPage
	 */
	public function __construct( SpecialPage $specialPage ) {
		$this->specialPage = $specialPage;
	}

	/**
	 * @param string $baseVersionText
	 * @param string $titleText
	 * @return string
	 */
	public function getHtml( $baseVersionText, $titleText ) {
		return Html::openElement(
			'form',
			[
				'action' => $this->specialPage->getPageTitle()->getLocalURL(),
				'method' => 'POST',
			]
		) .
		( new HtmlWikiTextEditor( $this->specialPage ) )->getHtml( $baseVersionText ) .
		Html::hidden(
			"mw-twocolconflict-test-title",
			$titleText
		) .
		Html::hidden(
			"wpEditToken",
			$this->specialPage->getUser()->getEditToken()
		) .
		( new ButtonInputWidget(
			[
				'label' => ( new Message( 'twoColConflict-test-text-submit' ) )->plain(),
				'type' => 'submit',
				'flags' => [ 'primary', 'progressive' ],
				'tabIndex' => 4
			]
		) )->addClasses( [ 'mw-twocolconflict-test-text-submit' ] ) .
		Html::closeElement( 'form' );
	}

}
