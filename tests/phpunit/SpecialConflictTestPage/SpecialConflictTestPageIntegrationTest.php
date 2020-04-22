<?php

namespace TwoColConflict\Tests\SpecialConflictTestPage;

use ExtensionRegistry;
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

	protected function setUp() : void {
		parent::setUp();
		$this->setUserLang( 'qqx' );

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

	public function testNoOutputWhenBetaFeatureAndNoUser() {
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' ) ) {
			$this->markTestSkipped();
		}

		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', true );

		[ $html, ] = $this->executeSpecialPage();

		$this->assertStringContainsString(
			'<div class="warningbox"><p>(twocolconflict-test-needsbeta)</p></div>',
			$html
		);
	}

}
