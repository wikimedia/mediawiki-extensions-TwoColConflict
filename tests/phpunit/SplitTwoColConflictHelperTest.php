<?php

namespace TwoColConflict\Tests;

use IBufferingStatsdDataFactory;
use Language;
use MediaWiki\CommentFormatter\CommentFormatter;
use MediaWiki\Content\IContentHandlerFactory;
use MediaWiki\Session\SessionId;
use MessageLocalizer;
use MockTitleTrait;
use OOUI\BlankTheme;
use OOUI\Theme;
use OutputPage;
use RawMessage;
use TwoColConflict\SplitTwoColConflictHelper;
use TwoColConflict\TalkPageConflict\ResolutionSuggester;
use TwoColConflict\TwoColConflictContext;
use WebRequest;

/**
 * @covers \TwoColConflict\SplitTwoColConflictHelper
 *
 * @group Database
 * @license GPL-2.0-or-later
 */
class SplitTwoColConflictHelperTest extends \MediaWikiIntegrationTestCase {
	use MockTitleTrait;

	protected function setUp(): void {
		parent::setUp();
		Theme::setSingleton( new BlankTheme() );
	}

	protected function tearDown(): void {
		Theme::setSingleton();
		parent::tearDown();
	}

	public function testBasics() {
		$helper = new SplitTwoColConflictHelper(
			$this->makeMockTitle( __CLASS__ ),
			$this->createOutputPage(),
			$this->createMock( IBufferingStatsdDataFactory::class ),
			'',
			$this->createMock( IContentHandlerFactory::class ),
			$this->createMock( TwoColConflictContext::class ),
			$this->createMock( ResolutionSuggester::class ),
			$this->createMock( CommentFormatter::class )
		);

		$this->assertSame( '', $helper->getExplainHeader() );
		$this->assertSame( '', $helper->getEditConflictMainTextBox() );
		// This should not trigger OutputPage::addHTML(), asserted above
		$helper->showEditFormTextAfterFooters();
	}

	public function testGetEditFormHtmlBeforeContent() {
		$helper = new SplitTwoColConflictHelper(
			$this->makeMockTitle( __CLASS__ ),
			$this->createOutputPage(),
			$this->createMock( IBufferingStatsdDataFactory::class ),
			'',
			$this->createMock( IContentHandlerFactory::class ),
			$this->createMock( TwoColConflictContext::class ),
			$this->createMock( ResolutionSuggester::class ),
			$this->createMock( CommentFormatter::class )
		);
		$helper->setTextboxes( '<YOURTEXT attribute="">', '<STOREDVERSION attribute="">' );

		$html = $helper->getEditFormHtmlBeforeContent();
		$this->assertStringContainsString( ' name="wpTextbox1"', $html );
		$this->assertStringContainsString( ' name="mw-twocolconflict-your-text"', $html );

		$this->assertStringContainsString( '&lt;YOURTEXT attribute="">', $html );
		$this->assertStringContainsString( '&lt;STOREDVERSION attribute=&quot;&quot;&gt;', $html );
		// Make sure there is no code path without escaping
		$this->assertStringNotContainsString( '<YOURTEXT attribute="">', $html );
		$this->assertStringNotContainsString( '<STOREDVERSION attribute="">', $html );
	}

	public function testGetEditFormHtmlAfterContent() {
		$out = $this->createOutputPage();
		$out->expects( $this->once() )->method( 'addModules' );

		$helper = new SplitTwoColConflictHelper(
			$this->makeMockTitle( __CLASS__ ),
			$out,
			$this->createMock( IBufferingStatsdDataFactory::class ),
			'',
			$this->createMock( IContentHandlerFactory::class ),
			$this->createMock( TwoColConflictContext::class ),
			$this->createMock( ResolutionSuggester::class ),
			$this->createMock( CommentFormatter::class )
		);

		$this->assertSame( '', $helper->getEditFormHtmlAfterContent() );
	}

	private function createOutputPage() {
		$localizer = new class implements MessageLocalizer {
			public function msg( $key, ...$params ) {
				return new RawMessage( '' );
			}
		};

		$request = $this->createMock( WebRequest::class );
		$request->method( 'getBool' )->willReturn( false );
		$request->method( 'getSessionId' )->willReturn( new SessionId( '' ) );

		$out = $this->createMock( OutputPage::class );
		$out->expects( $this->never() )->method( 'addHTML' );
		$out->method( 'getUser' )->willReturn( $this->getTestUser()->getUser() );
		$out->method( 'getLanguage' )->willReturn( $this->createMock( Language::class ) );
		$out->method( 'getContext' )->willReturn( $localizer );
		$out->method( 'getRequest' )->willReturn( $request );
		return $out;
	}

}
