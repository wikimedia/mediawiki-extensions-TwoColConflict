<?php

namespace TwoColConflict\Tests;

use Message;
use MessageLocalizer;
use OOUI\BlankTheme;
use OOUI\Theme;
use TwoColConflict\Html\CoreUiHintHtml;

/**
 * @covers \TwoColConflict\Html\CoreUiHintHtml
 */
class CoreUiHintHtmlTest extends \MediaWikiUnitTestCase {

	protected function setUp() : void {
		parent::setUp();
		Theme::setSingleton( new BlankTheme() );
	}

	protected function tearDown() : void {
		Theme::setSingleton( null );
		parent::tearDown();
	}

	public function testGetHtml() {
		$messageLocalizer = $this->createMock( MessageLocalizer::class );
		$messageLocalizer->method( 'msg' )->willReturnCallback( function ( $key ) {
			$msg = $this->createMock( Message::class );
			$msg->method( 'parse' )->willReturn( "<a>" );
			$msg->method( 'text' )->willReturn( "<$key>" );
			return $msg;
		} );

		$html = ( new CoreUiHintHtml( $messageLocalizer ) )->getHtml();
		$this->assertStringContainsString( ' type="checkbox"', $html );
		$this->assertStringContainsString( '<a target="_blank">', $html );
		$this->assertStringContainsString( '&lt;twocolconflict-core-ui-hint-close&gt;', $html );
	}

}
