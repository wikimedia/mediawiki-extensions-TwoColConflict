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
	 * @var string
	 */
	private $defaultValue;

	/**
	 * @param SpecialPage $specialPage
	 * @param string $defaultValue
	 */
	public function __construct( SpecialPage $specialPage, $defaultValue = '' ) {
		$this->specialPage = $specialPage;
		$this->defaultValue = $defaultValue;
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
						'value' => $this->defaultValue,
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
