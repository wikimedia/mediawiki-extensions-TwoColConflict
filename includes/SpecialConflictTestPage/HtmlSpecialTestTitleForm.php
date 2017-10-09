<?php

use OOUI\ButtonInputWidget;
use OOUI\FieldLayout;
use OOUI\FieldsetLayout;
use OOUI\TextInputWidget;

/**
 * Form allowing the load an article as base version for the conflict test.
 */
class HtmlSpecialTestTitleForm {

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
	 * @return string
	 */
	public function getHtml() {
		return Html::openElement(
			'form',
			[
				'action' => $this->specialPage->getPageTitle()->getLocalURL(),
				'method' => 'POST',
			]
		) .
		( new FieldsetLayout( [
			'items' => [ new FieldLayout(
				new TextInputWidget(
					[
						'name' => 'mw-twocolconflict-test-title',
						'classes' => [ 'mw-twocolconflict-test-title' ],
						'suggestions' => false,
						'autofocus' => true,
						'required' => true,
					]
				),
				[
					'align' => 'top',
					'label' => ( new Message( 'twoColConflict-test-title-label' ) )->plain(),
				]
			) ]
		] ) ) .
		Html::hidden(
			"wpEditToken",
			$this->specialPage->getUser()->getEditToken()
		) .
		( new ButtonInputWidget(
		[
			'label' => ( new Message( 'twoColConflict-test-title-submit' ) )->plain(),
			'type' => 'submit',
			'flags' => [ 'primary', 'progressive' ],
		]
		) )->addClasses( [ 'mw-twocolconflict-test-title-submit' ] ) .
		Html::closeElement( 'form' );
	}

}
