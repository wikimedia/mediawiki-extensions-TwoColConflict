<?php

namespace TwoColConflict\SpecialConflictTestPage;

use Html;
use Message;
use OOUI\ButtonInputWidget;
use OOUI\FieldLayout;
use OOUI\FieldsetLayout;
use OOUI\TextInputWidget;
use SpecialPage;

/**
 * Form allowing to load an article as base version for the conflict test.
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
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
	 * @return string HTML
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
					'label' => ( new Message( 'twocolconflict-test-title-label' ) )->plain(),
				]
			) ]
		] ) ) .
		Html::hidden(
			"wpEditToken",
			$this->specialPage->getUser()->getEditToken()
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
