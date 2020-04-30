<?php

namespace TwoColConflict\Tests;

use EditPage;
use ExtensionRegistry;
use IContextSource;
use OOUI\InputWidget;
use OutputPage;
use PHPUnit\Framework\MockObject\MockObject;
use Title;
use TwoColConflict\TwoColConflictContext;
use TwoColConflict\TwoColConflictHooks;
use User;
use WebRequest;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\TwoColConflictHooks
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictHooksTest extends \MediaWikiIntegrationTestCase {

	protected function setUp() : void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgTwoColConflictBetaFeature' => false,
			'wgTwoColConflictSuggestResolution' => true,
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
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' ) ) {
			$this->markTestSkipped( 'BetaFeatures not loaded' );
		}

		$this->setMwGlobals( [
			'wgTwoColConflictBetaFeature' => true,
			'wgExtensionAssetsPath' => '',
		] );

		$prefs = [];
		TwoColConflictHooks::onGetBetaFeaturePreferences( $this->createMock( User::class ), $prefs );
		$this->assertArrayHasKey( TwoColConflictContext::BETA_PREFERENCE_NAME, $prefs );
	}

	public function testOnGetBetaFeaturePreferences_withBetaDisabled() {
		$prefs = [];
		TwoColConflictHooks::onGetBetaFeaturePreferences( $this->createMock( User::class ), $prefs );
		$this->assertArrayNotHasKey( TwoColConflictContext::BETA_PREFERENCE_NAME, $prefs );
	}

	public function testOnGetPreferences_whileInBeta() {
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' ) ) {
			$this->markTestSkipped( 'BetaFeatures not loaded' );
		}

		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', true );

		$prefs = [];
		TwoColConflictHooks::onGetPreferences( $this->createMock( User::class ), $prefs );
		$this->assertArrayNotHasKey( TwoColConflictContext::ENABLED_PREFERENCE, $prefs );
	}

	public function testOnGetPreferences() {
		$prefs = [];
		TwoColConflictHooks::onGetPreferences( $this->createMock( User::class ), $prefs );
		$this->assertArrayHasKey( TwoColConflictContext::ENABLED_PREFERENCE, $prefs );
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
						[ 'other' => 'c', 'bad key' => 'bad value' ],
						'bad string',
					],
				],
				"b\nbad value\nbad string"
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
		$request = $this->createRequest( $requestData );
		TwoColConflictHooks::onImportFormData( $editPage, $request );
		$this->assertSame( $expectedWikitext, $editPage->textbox1 );
	}

	public function provideTalkPageRequests() {
		return [
			'empty' => [
				'contentRows' => [],
				'extraLineFeeds' => [],
				'expected' => [ [], [] ]
			],
			'other first' => [
				'contentRows' => [
					[ 'other' => '' ],
					[ 'your' => '' ],
				],
				'extraLineFeeds' => [ 1, 2 ],
				'expected' => [
					[
						[ 'your' => '' ],
						[ 'other' => '' ],
					],
					[ 2, 1 ]
				]
			],
			'leave copy rows untouched' => [
				'contentRows' => [
					[ 'copy' => 'a' ],
					[ 'copy' => 'b' ],
					[ 'other' => '' ],
					[ 'your' => '' ],
					[ 'copy' => 'c' ],
				],
				'extraLineFeeds' => [ 1, 2, 3, 4, 5 ],
				'expected' => [
					[
						[ 'copy' => 'a' ],
						[ 'copy' => 'b' ],
						[ 'your' => '' ],
						[ 'other' => '' ],
						[ 'copy' => 'c' ],
					],
					[ 1, 2, 4, 3, 5 ]
				]
			],
			'multiple pairs to swap' => [
				'contentRows' => [
					[ 'other' => 'a' ],
					[ 'your' => 'b' ],
					[ 'other' => 'c' ],
					[ 'your' => 'd' ],
				],
				'extraLineFeeds' => [],
				'expected' => [
					[
						[ 'your' => 'b' ],
						[ 'other' => 'a' ],
						[ 'your' => 'd' ],
						[ 'other' => 'c' ],
					],
					[ 0, 0, 0, 0 ]
				]
			],
			'do not swap back if my row is alreday before' => [
				'contentRows' => [
					[ 'your' => '' ],
					[ 'other' => '' ],
				],
				'extraLineFeeds' => [],
				'expected' => [
					[
						[ 'your' => '' ],
						[ 'other' => '' ],
					],
					[]
				]
			],
		];
	}

	/**
	 * @dataProvider provideTalkPageRequests
	 */
	public function testSwapTalkComments(
		array $contentRows,
		array $extraLineFeeds,
		array $expected
	) {
		/** @var TwoColConflictHooks $hooks */
		$hooks = TestingAccessWrapper::newFromClass( TwoColConflictHooks::class );
		$this->assertSame( $expected, $hooks->swapTalkComments( $contentRows, $extraLineFeeds ) );
	}

	public function testOnUserGetDefaultOptions() {
		$prefs = [];
		TwoColConflictHooks::onUserGetDefaultOptions( $prefs );
		$this->assertArrayHasKey( TwoColConflictContext::ENABLED_PREFERENCE, $prefs );
	}

	/**
	 * @param bool $enabled
	 *
	 * @return IContextSource|MockObject
	 */
	private function createContext( bool $enabled = true ) {
		$user = $this->createMock( User::class );
		$user->method( 'getBoolOption' )
			->with( TwoColConflictContext::ENABLED_PREFERENCE )
			->willReturn( $enabled );

		$context = $this->createMock( IContextSource::class );
		$context->method( 'getUser' )->willReturn( $user );
		return $context;
	}

	/**
	 * @param array $requestParams
	 *
	 * @return WebRequest
	 */
	private function createRequest( array $requestParams ) {
		$request = $this->createMock( WebRequest::class );
		$getter = function ( string $name, $default ) use ( $requestParams ) {
			return $requestParams[$name] ?? $default;
		};
		$request->method( 'getArray' )->willReturnCallback( $getter );
		$request->method( 'getBool' )->willReturnCallback( $getter );
		return $request;
	}

}
