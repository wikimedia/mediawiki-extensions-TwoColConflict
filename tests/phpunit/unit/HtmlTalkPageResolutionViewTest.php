<?php

namespace TwoColConflict\Tests;

use MediaWiki\Message\Message;
use MessageLocalizer;
use OOUI\BlankTheme;
use OOUI\Theme;
use TwoColConflict\Html\HtmlEditableTextComponent;
use TwoColConflict\Html\HtmlTalkPageResolutionView;

/**
 * @covers \TwoColConflict\Html\HtmlTalkPageResolutionView
 *
 * @license GPL-2.0-or-later
 */
class HtmlTalkPageResolutionViewTest extends \MediaWikiUnitTestCase {

	protected function setUp(): void {
		parent::setUp();
		// intentionally not reset in teardown, see Icb6901f4d5
		Theme::setSingleton( new BlankTheme() );
	}

	public static function provideDiffs() {
		return [
			'empty' => [ [], 0, 0, [] ],
			'single row' => [
				[
					[ 'newtext' => 'a' ],
				],
				0,
				0,
				[
					// Not every order is critical here, just make sure everything is there
					' class="mw-twocolconflict-split-view mw-twocolconflict-single-column-view"',
					'<textarea>a</textarea>',

					// Form elements and labels required for the no-JavaScript workflow
					' class="mw-twocolconflict-order-selector"',
					'(twocolconflict-talk-reorder-prompt)',
					" name='mw-twocolconflict-reorder'",
					" value='reverse'",
					"(twocolconflict-talk-reverse-order)",
					" value='no-change'",
					"(twocolconflict-talk-same-order)",

					' name="mw-twocolconflict-single-column-view"',
				]
			],
			'typical talk page conflict' => [
				[
					[ 'copytext' => 'a' ],
					[ 'newtext' => 'b' ],
					[ 'newtext' => 'c' ],
					[ 'copytext' => 'd' ],
				],
				1,
				2,
				[
					'<textarea>a</textarea>',
					"(twocolconflict-talk-conflicting)",
					'<textarea>b</textarea>',
					"(twocolconflict-talk-your)",
					'<textarea>c</textarea>',
					'<textarea>d</textarea>',
				]
			],
			'multiple copy rows' => [
				[
					[ 'copytext' => 'a' ],
					[ 'copytext' => 'b' ],
					[ 'newtext' => 'c' ],
					[ 'newtext' => 'd' ],
					[ 'copytext' => 'e' ],
					[ 'copytext' => 'f' ],
				],
				2,
				3,
				[
					'<textarea>a</textarea>',
					'<textarea>b</textarea>',
					"(twocolconflict-talk-conflicting)",
					'<textarea>c</textarea>',
					"(twocolconflict-talk-your)",
					'<textarea>d</textarea>',
					'<textarea>e</textarea>',
					'<textarea>f</textarea>',
				]
			],
			'should not make bogus input with >2 conflicting rows worse' => [
				[
					[ 'copytext' => 'a' ],
					[ 'copytext' => 'b' ],
					[ 'copytext' => 'c' ],
					[ 'copytext' => 'd' ],
					[ 'copytext' => 'e' ],
				],
				1,
				3,
				[
					'<textarea>a</textarea>',
					"(twocolconflict-talk-conflicting)",
					'<textarea>b</textarea>',
					'<textarea>c</textarea>',
					"(twocolconflict-talk-your)",
					'<textarea>d</textarea>',
					'<textarea>e</textarea>',
				]
			],
		];
	}

	/**
	 * @dataProvider provideDiffs
	 */
	public function testGetHtml( array $diff, int $otherIndex, int $yourIndex, array $expected ) {
		$editableTextComponent = $this->createMock( HtmlEditableTextComponent::class );
		$editableTextComponent->method( 'getHtml' )->willReturnCallback( static function ( $_, $text ) {
			// We don't care about escaping here, that's tested in HtmlEditableTextComponentTest
			return "<textarea>$text</textarea>";
		} );

		$localizer = $this->createMock( MessageLocalizer::class );
		$localizer->method( 'msg' )->willReturnCallback( function ( $key ) {
			$msg = $this->createMock( Message::class );
			$msg->method( 'text' )->willReturn( "($key)" );
			$msg->method( 'parse' )->willReturn( '' );
			return $msg;
		} );

		$view = new HtmlTalkPageResolutionView( $editableTextComponent, $localizer );
		$html = $view->getHtml( $diff, $otherIndex, $yourIndex, false );

		$this->assertStringContainsString( ' name="mw-twocolconflict-single-column-view"', $html,
			'form identifier' );
		$this->assertElementsPresentInOrder( $html, $expected );
	}

	public function assertElementsPresentInOrder( string $html, array $expectedElements ) {
		$offset = 0;
		foreach ( $expectedElements as $element ) {
			$pos = strpos( $html, $element, $offset );
			$this->assertIsInt( $pos, "$element not found after position " . $offset .
				': …' . substr( $html, $offset, 1000 ) . '…' );
			$offset = $pos + strlen( $element );
		}
	}

}
