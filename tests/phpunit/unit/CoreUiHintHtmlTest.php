<?php

namespace TwoColConflict\Tests;

use MediaWiki\Message\Message;
use MessageLocalizer;
use OOUI\BlankTheme;
use OOUI\Theme;
use TwoColConflict\Html\CoreUiHintHtml;

/**
 * @covers \TwoColConflict\Html\CoreUiHintHtml
 *
 * @license GPL-2.0-or-later
 */
class CoreUiHintHtmlTest extends \MediaWikiUnitTestCase {

	protected function setUp(): void {
		parent::setUp();
		// intentionally not reset in teardown, see Icb6901f4d5
		Theme::setSingleton( new BlankTheme() );
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
