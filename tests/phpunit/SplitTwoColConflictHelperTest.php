<?php

namespace TwoColConflict\Tests;

use IBufferingStatsdDataFactory;
use Language;
use MediaWiki\Content\IContentHandlerFactory;
use Message;
use MessageLocalizer;
use OOUI\BlankTheme;
use OOUI\Theme;
use OutputPage;
use Title;
use TwoColConflict\SplitTwoColConflict\ResolutionSuggester;
use TwoColConflict\SplitTwoColConflict\SplitTwoColConflictHelper;
use User;
use WebRequest;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\SplitTwoColConflictHelper
 */
class SplitTwoColConflictHelperTest extends \MediaWikiIntegrationTestCase {

	protected function setUp() : void {
		parent::setUp();
		Theme::setSingleton( new BlankTheme() );
	}

	protected function tearDown() : void {
		Theme::setSingleton( null );
		parent::tearDown();
	}

	public function testBasics() {
		$helper = new SplitTwoColConflictHelper(
			$this->createTitle(),
			$this->createOutputPage(),
			$this->createMock( IBufferingStatsdDataFactory::class ),
			'',
			'',
			$this->createMock( IContentHandlerFactory::class ),
			$this->createMock( ResolutionSuggester::class )
		);

		$this->assertSame( '', $helper->getExplainHeader() );
		$this->assertSame( '', $helper->getEditConflictMainTextBox() );
		// This should not trigger OutputPage::addHTML(), asserted above
		$helper->showEditFormTextAfterFooters();
	}

	public function testGetEditFormHtmlBeforeContent() {
		$helper = new SplitTwoColConflictHelper(
			$this->createTitle(),
			$this->createOutputPage(),
			$this->createMock( IBufferingStatsdDataFactory::class ),
			'',
			'',
			$this->createMock( IContentHandlerFactory::class ),
			$this->createMock( ResolutionSuggester::class )
		);
		$helper->setTextboxes( '<YOURTEXT>', '<STOREDVERSION>' );

		$html = $helper->getEditFormHtmlBeforeContent();
		$this->assertStringContainsString( ' name="wpTextbox1"', $html );
		$this->assertStringContainsString( ' name="mw-twocolconflict-current-text"', $html );
		$this->assertStringContainsString( ' name="mw-twocolconflict-your-text"', $html );

		$this->assertStringContainsString( '&lt;YOURTEXT&gt;', $html );
		$this->assertStringContainsString( '&lt;STOREDVERSION&gt;', $html );
		// Make sure there is no code path without escaping
		$this->assertStringNotContainsString( '<YOURTEXT>', $html );
		$this->assertStringNotContainsString( '<STOREDVERSION>', $html );
	}

	public function testGetEditFormHtmlAfterContent() {
		$out = $this->createOutputPage();
		$out->expects( $this->once() )->method( 'addModules' );

		$helper = new SplitTwoColConflictHelper(
			$this->createTitle(),
			$out,
			$this->createMock( IBufferingStatsdDataFactory::class ),
			'',
			'',
			$this->createMock( IContentHandlerFactory::class ),
			$this->createMock( ResolutionSuggester::class )
		);

		$this->assertSame( '', $helper->getEditFormHtmlAfterContent() );
	}

	private function createTitle() {
		$title = $this->createMock( Title::class );
		$title->method( 'getContentModel' )->willReturn( '' );
		return $title;
	}

	private function createOutputPage() {
		$msg = $this->createMock( Message::class );
		$msg->method( 'parse' )->willReturn( '' );
		$msg->method( 'rawParams' )->willReturnSelf();

		$localizer = $this->createMock( MessageLocalizer::class );
		$localizer->method( 'msg' )->willReturn( $msg );

		$user = $this->createMock( User::class );
		$user->method( 'getOption' )->willReturn( '' );

		$out = $this->createMock( OutputPage::class );
		$out->expects( $this->never() )->method( 'addHTML' );
		$out->method( 'getUser' )->willReturn( $user );
		$out->method( 'getLanguage' )->willReturn( $this->createMock( Language::class ) );
		$out->method( 'getContext' )->willReturn( $localizer );
		$out->method( 'getRequest' )->willReturn( $this->createMock( WebRequest::class ) );
		return $out;
	}

}
