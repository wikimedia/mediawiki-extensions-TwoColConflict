<?php

namespace TwoColConflict\SpecialConflictTestPage;

use Html;
use Message;
use OOUI\ButtonInputWidget;
use SpecialPage;

/**
 * Form allowing the user to change the base text for the conflict.
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
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
	 *
	 * @return string HTML
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
				'label' => ( new Message( 'twocolconflict-test-text-submit' ) )->plain(),
				'type' => 'submit',
				'flags' => [ 'primary', 'progressive' ],
				'tabIndex' => 4
			]
		) )->addClasses( [ 'mw-twocolconflict-test-text-submit' ] ) .
		Html::closeElement( 'form' );
	}

}
