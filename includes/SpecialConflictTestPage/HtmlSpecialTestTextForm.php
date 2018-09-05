<?php

namespace TwoColConflict\SpecialConflictTestPage;

use Html;
use Message;
use OOUI\ButtonInputWidget;
use TwoColConflict\SpecialPageHtmlFragment;

/**
 * Form allowing the user to change the base text for the conflict.
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlSpecialTestTextForm extends SpecialPageHtmlFragment {

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
				'action' => $this->getPageTitle()->getLocalURL(),
				'method' => 'POST',
			]
		) .
		( new HtmlWikiTextEditor( $this ) )->getHtml( $baseVersionText ) .
		Html::hidden(
			"mw-twocolconflict-test-title",
			$titleText
		) .
		Html::hidden(
			"wpEditToken",
			$this->getUser()->getEditToken()
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
