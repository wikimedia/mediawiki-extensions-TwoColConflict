<?php

namespace TwoColConflict\Tests\SpecialConflictTestPage;

use HamcrestPHPUnitIntegration;
use SpecialPage;
use SpecialPageTestBase;
use TwoColConflict\SpecialConflictTestPage\SpecialConflictTestPage;

/**
 * @covers \TwoColConflict\SpecialConflictTestPage\SpecialConflictTestPage
 * @covers \TwoColConflict\SpecialPageHtmlFragment
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SpecialConflictTestPageIntegrationTest extends SpecialPageTestBase {
	use HamcrestPHPUnitIntegration;

	protected function setUp() : void {
		parent::setUp();

		// register a namespace with non-editable content model to test T182668
		$this->mergeMwGlobalArrayValue( 'wgExtraNamespaces', [
			12312 => 'Dummy',
			12313 => 'Dummy_talk',
		] );
		$this->mergeMwGlobalArrayValue( 'wgNamespaceContentModels', [
			12312 => 'testing',
		] );
		$this->mergeMwGlobalArrayValue( 'wgContentHandlers', [
			'testing' => 'DummyContentHandlerForTesting',
		] );
	}

	/**
	 * Returns a new instance of the special page under test.
	 *
	 * @return SpecialPage
	 */
	protected function newSpecialPage() {
		return new SpecialConflictTestPage();
	}

	private function assertWarningBox( $html, $text ) {
		$this->assertThatHamcrest(
			$html,
			is( htmlPiece( havingChild(
				both( withTagName( 'div' ) )
					->andAlso( withClass( 'warningbox' ) )
					->andAlso( havingChild(
						both( withTagName( 'p' ) )
							->andAlso( havingTextContents( $text ) )
					) )
			) ) )
		);
	}

	public function testNoOutputWhenBetaFeatureAndNoUser() {
		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', true );

		/** @var string $html */
		/** @var \WebResponse $response */
		list( $html, $response ) = $this->executeSpecialPage();

		$this->assertWarningBox(
			$html,
			'You must enable the \'Two column edit conflict\' ' .
			'beta feature in your preferences to use this special page.'
		);
	}

}
