<?php

namespace TwoColConflict\Tests;

use EditPage;
use ExtensionRegistry;
use FauxRequest;
use IContextSource;
use OOUI\InputWidget;
use OutputPage;
use PHPUnit\Framework\MockObject\MockObject;
use Title;
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

	public function testOnAlternateEdit_withFeatureDisabled() {
		$editPage = $this->createMock( EditPage::class );
		$editPage->method( 'getContext' )->willReturn( $this->createContext( false ) );
		$editPage->method( 'getTitle' )->willReturn( $this->createMock( Title::class ) );
		$editPage->expects( $this->never() )->method( 'setEditConflictHelperFactory' );

		TwoColConflictHooks::onAlternateEdit( $editPage );
	}

	public function testOnAlternateEdit_withInvalidRequest() {
		$request = $this->createMock( WebRequest::class );
		$request->method( 'getArray' )->with( 'mw-twocolconflict-split-content' )->willReturn( [] );
		$request->method( 'getInt' )->with( 'parentRevId' )->willReturn( 1 );
		$request->expects( $this->once() )->method( 'setVal' )->with( 'editRevId', 1 );

		$context = $this->createContext();
		$context->method( 'getRequest' )->willReturn( $request );

		$editPage = $this->createMock( EditPage::class );
		$editPage->method( 'getContext' )->willReturn( $context );
		$editPage->method( 'getTitle' )->willReturn( $this->createMock( Title::class ) );
		// TODO: The code in the factory function is currently not tested
		$editPage->expects( $this->once() )->method( 'setEditConflictHelperFactory' );

		TwoColConflictHooks::onAlternateEdit( $editPage );
	}

	public function testOnEditPageBeforeEditButtons() {
		$editPage = $this->createMock( EditPage::class );
		$editPage->isConflict = true;
		$editPage->method( 'getContext' )->willReturn( $this->createContext() );
		$editPage->method( 'getTitle' )->willReturn( $this->createMock( Title::class ) );

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

	public function testOnGetBetaFeaturePreferences_whileInBeta() {
		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', true );
		$prefs = [];
		TwoColConflictHooks::onGetBetaFeaturePreferences( $this->getTestUser()->getUser(), $prefs );
		$this->assertArrayHasKey( 'twocolconflict', $prefs );
	}

	public function testOnGetBetaFeaturePreferences_withBetaDisabled() {
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

			'trivial copy situation' => [
				[
					'mw-twocolconflict-side-selector' => [],
					'mw-twocolconflict-split-content' => [
						1 => [ 'copy' => 'abc' ],
					],
					'mw-twocolconflict-split-linefeeds' => [
						1 => [ 'copy' => 0 ],
					],
				],
				'abc',
			],
			'trailing linefeeds in the content are ignored' => [
				[
					'mw-twocolconflict-side-selector' => [
						1 => 'other',
					],
					'mw-twocolconflict-split-content' => [
						1 => [ 'other' => "abc\n", 'your' => 'def' ],
					],
					'mw-twocolconflict-split-linefeeds' => [
						1 => [ 'other' => 0 ],
					],
				],
				'abc',
			],
			'original trailing linefeed is restored' => [
				[
					'mw-twocolconflict-side-selector' => [
						1 => 'other',
					],
					'mw-twocolconflict-split-content' => [
						1 => [ 'other' => "abc\n\n", 'your' => 'def' ],
					],
					'mw-twocolconflict-split-linefeeds' => [
						1 => [ 'other' => 1 ],
					],
				],
				"abc\n",
			],
			'all possibilities in one request' => [
				[
					'mw-twocolconflict-side-selector' => [
						2 => 'other',
						4 => 'your',
					],
					'mw-twocolconflict-split-content' => [
						1 => [ 'copy' => 'a' ],
						2 => [ 'other' => 'b other', 'your' => 'b your' ],
						3 => [ 'copy' => 'c' ],
						4 => [ 'other' => 'd other', 'your' => 'd your' ],
					],
					'mw-twocolconflict-split-linefeeds' => [
						4 => [ 'your' => 0 ],
					],
				],
				"a\nb other\nc\nd your",
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

	public function testOnUserGetDefaultOptions() {
		$prefs = [];
		TwoColConflictHooks::onUserGetDefaultOptions( $prefs );
		$this->assertArrayHasKey( 'twocolconflict-enabled', $prefs );
	}

	/**
	 * @param bool $enabled
	 *
	 * @return IContextSource|MockObject
	 */
	private function createContext( bool $enabled = true ) {
		$user = $this->getTestUser()->getUser();
		$user->setOption( 'twocolconflict-enabled', $enabled );

		$context = $this->createMock( IContextSource::class );
		$context->method( 'getUser' )->willReturn( $user );
		return $context;
	}

}
