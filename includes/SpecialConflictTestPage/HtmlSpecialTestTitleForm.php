<?php

namespace TwoColConflict\SpecialConflictTestPage;

use Html;
use Message;
use OOUI\ButtonInputWidget;
use OOUI\FieldLayout;
use OOUI\FieldsetLayout;
use OOUI\TextInputWidget;
use TwoColConflict\SpecialPageHtmlFragment;

/**
 * Form allowing to load an article as base version for the conflict test.
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlSpecialTestTitleForm extends SpecialPageHtmlFragment {

	/**
	 * @param string $defaultValue
	 *
	 * @return string HTML
	 */
	public function getHtml( $defaultValue ) {
		return Html::openElement(
			'form',
			[
				'action' => $this->getPageTitle()->getLocalURL(),
				'method' => 'POST',
			]
		) .
		( new FieldsetLayout( [
			'items' => [ new FieldLayout(
				new TextInputWidget(
					[
						'name' => 'mw-twocolconflict-test-title',
						'value' => $defaultValue,
						'classes' => [ 'mw-twocolconflict-test-title' ],
						'suggestions' => false,
						'autofocus' => true,
						'required' => true,
					]
				),
				[
					'align' => 'top',
					'label' => ( new Message( 'twocolconflict-test-title-label' ) )->plain(),
				]
			) ]
		] ) ) .
		Html::hidden(
			"wpEditToken",
			$this->getUser()->getEditToken()
		) .
		( new ButtonInputWidget(
		[
			'label' => ( new Message( 'twocolconflict-test-title-submit' ) )->plain(),
			'type' => 'submit',
			'flags' => [ 'primary', 'progressive' ],
		]
		) )->addClasses( [ 'mw-twocolconflict-test-title-submit' ] ) .
		Html::closeElement( 'form' );
	}

}
