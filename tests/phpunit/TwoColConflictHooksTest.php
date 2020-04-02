<?php

namespace TwoColConflict\Tests;

use EditPage;
use ExtensionRegistry;
use FauxRequest;
use IContextSource;
use OOUI\InputWidget;
use OutputPage;
use PHPUnit\Framework\MockObject\MockObject;
use TwoColConflict\TwoColConflictHooks;
use WebRequest;

/**
 * @covers \TwoColConflict\TwoColConflictHooks
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictHooksTest extends \MediaWikiTestCase {

	protected function setUp() : void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgTwoColConflictBetaFeature' => false,
		] );
	}

	public function testOnAlternateEdit() {
		$request = $this->createMock( WebRequest::class );
		$request->method( 'getArray' )->with( 'mw-twocolconflict-split-content' )->willReturn( [] );
		$request->method( 'getInt' )->with( 'parentRevId' )->willReturn( 1 );
		$request->expects( $this->once() )->method( 'setVal' )->with( 'editRevId', 1 );

		$context = $this->createContext();
		$context->method( 'getRequest' )->willReturn( $request );

		$editPage = $this->createMock( EditPage::class );
		$editPage->method( 'getContext' )->willReturn( $context );
		// TODO: The code in the factory function is currently not tested
		$editPage->expects( $this->once() )->method( 'setEditConflictHelperFactory' );

		TwoColConflictHooks::onAlternateEdit( $editPage );
	}

	public function testOnEditPageBeforeEditButtons() {
		$editPage = $this->createMock( EditPage::class );
		$editPage->isConflict = true;
		$editPage->method( 'getContext' )->willReturn( $this->createContext() );

		$previewButton = $this->createMock( InputWidget::class );
		$previewButton->expects( $this->once() )->method( 'setDisabled' );

		$buttons = [ 'diff' => null, 'preview' => $previewButton ];
		TwoColConflictHooks::onEditPageBeforeEditButtons( $editPage, $buttons, $tabIndex );
		$this->assertArrayNotHasKey( 'diff', $buttons );
	}

	public function testOnEditPageshowEditFormInitial() {
		$calls = ExtensionRegistry::getInstance()->isLoaded( 'EventLogging' ) ? 1 : 0;
		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->expects( $this->exactly( $calls ) )->method( 'addModules' );

		TwoColConflictHooks::onEditPageshowEditFormInitial(
			$this->createMock( EditPage::class ),
			$outputPage
		);
	}

	public function testOnGetBetaFeaturePreferences() {
		$prefs = [];
		TwoColConflictHooks::onGetBetaFeaturePreferences( $this->getTestUser()->getUser(), $prefs );
		$this->assertArrayNotHasKey( 'twocolconflict', $prefs );
	}

	public function testOnGetPreferences() {
		$prefs = [];
		TwoColConflictHooks::onGetPreferences( $this->getTestUser()->getUser(), $prefs );
		$this->assertArrayHasKey( 'twocolconflict-enabled', $prefs );
	}

	public function provideWebRequests() {
		return [
			'empty request' => [ [], '' ],
			'no content' => [ [ 'mw-twocolconflict-split-content' => [], ], '' ],
			'minimal non-empty request' => [
				[
					'mw-twocolconflict-split-content' => [ [ 'copy' => 'a' ] ],
				],
				'a'
			],
			'bad non-array elements' => [
				[
					'mw-twocolconflict-split-content' => [ 'bad' ],
					'mw-twocolconflict-split-linefeeds' => [ 1 ],
				],
				"bad\n"
			],
			'valid single column request' => [
				[
					'mw-twocolconflict-single-column-view' => true,
					'mw-twocolconflict-split-content' => [ [ 'other' => 'a' ] ],
				],
				'a'
			],
			'single column request with invalid content' => [
				[
					'mw-twocolconflict-single-column-view' => true,
					'mw-twocolconflict-split-content' => [
						[ 'other' => 'a' ],
						[ 'your' => 'b' ],
						'bad',
					],
				],
				"b\nbad"
			],
			'single column request with invalid side selection' => [
				[
					'mw-twocolconflict-single-column-view' => true,
					'mw-twocolconflict-split-content' => [
						[ 'other' => 'a' ],
						[ 'your' => 'b' ],
					],
					'mw-twocolconflict-side-selector' => [ 'bad' ],
				],
				'b'
			],
		];
	}

	/**
	 * @dataProvider provideWebRequests
	 */
	public function testOnImportFormData( array $requestData, string $expectedWikitext ) {
		$editPage = $this->createMock( EditPage::class );
		$request = new FauxRequest( $requestData );
		TwoColConflictHooks::onImportFormData( $editPage, $request );
		$this->assertSame( $expectedWikitext, $editPage->textbox1 );
	}

	public function provideOnImportFormData() {
		return [
			[
				null,
				null,
				null,
				'',
			],
			[
				[],
				[
					1 => [ 'copy' => 'abc' ],
				],
				[
					1 => [ 'copy' => 0 ],
				],
				"abc",
			],
			[
				[
					1 => 'other',
				],
				[
					1 => [ 'other' => "abc\n", 'your' => 'def' ],
				],
				[
					1 => [ 'other' => 0 ],
				],
				"abc",
			],
			[
				[
					1 => 'other',
				],
				[
					1 => [ 'other' => "abc\n\n", 'your' => 'def' ],
				],
				[
					1 => [ 'other' => 1 ],
				],
				"abc\n",
			],
			[
				[
					2 => 'other',
					4 => 'your',
				],
				[
					1 => [ 'copy' => 'a' ],
					2 => [ 'other' => 'b other', 'your' => 'b your' ],
					3 => [ 'copy' => 'c' ],
					4 => [ 'other' => 'd other', 'your' => 'd your' ],
				],
				[
					4 => [ 'your' => 0 ],
				],
				"a\nb other\nc\nd your",
			],
		];
	}

	/**
	 * @dataProvider provideOnImportFormData
	 */
	public function testOnImportFormData_legacy(
		?array $sideSelection,
		?array $splitContent,
		?array $splitLineFeeds,
		string $expected
	) {
		$editPage = $this->createEditPage();
		$request = $this->createWebRequest( $sideSelection, $splitContent, $splitLineFeeds );

		TwoColConflictHooks::onImportFormData( $editPage, $request );
		$this->assertSame( $expected, $editPage->textbox1 );
	}

	public function testOnUserGetDefaultOptions() {
		$prefs = [];
		TwoColConflictHooks::onUserGetDefaultOptions( $prefs );
		$this->assertArrayHasKey( 'twocolconflict-enabled', $prefs );
	}

	/**
	 * @return EditPage
	 */
	private function createEditPage() {
		return $this->createMock( EditPage::class );
	}

	/**
	 * @return IContextSource|MockObject
	 */
	private function createContext() {
		$context = $this->createMock( IContextSource::class );
		$context->method( 'getUser' )->willReturn( $this->getTestUser()->getUser() );
		return $context;
	}

	/**
	 * @return \WebRequest
	 */
	private function createWebRequest(
		array $sideSelection = null,
		array $splitContent = null,
		array $splitLineFeeds = null
	) {
		return new \FauxRequest( [
			'mw-twocolconflict-side-selector' => $sideSelection,
			'mw-twocolconflict-split-content' => $splitContent,
			'mw-twocolconflict-split-linefeeds' => $splitLineFeeds,
		] );
	}

}
