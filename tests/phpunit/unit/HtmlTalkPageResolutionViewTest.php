<?php

namespace TwoColConflict\Tests;

use Message;
use MessageLocalizer;
use OOUI\BlankTheme;
use OOUI\Theme;
use TwoColConflict\SplitTwoColConflict\HtmlEditableTextComponent;
use TwoColConflict\SplitTwoColConflict\HtmlTalkPageResolutionView;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\HtmlTalkPageResolutionView
 */
class HtmlTalkPageResolutionViewTest extends \MediaWikiUnitTestCase {

	protected function setUp() : void {
		parent::setUp();

		Theme::setSingleton( new BlankTheme() );
	}

	protected function tearDown() : void {
		Theme::setSingleton( null );

		parent::tearDown();
	}

	public function provideDiffs() {
		return [
			'empty' => [ [], 0, 0, [] ],
			'single row' => [
				[
					[ 'newtext' => 'a' ],
				],
				0,
				0,
				[
					// FIXME: This <div> is not closed
					'<div class="mw-twocolconflict-suggestion-draggable">',
					'<textarea>a</textarea>',
					// '</div></div></div>',
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
					'<div class="mw-twocolconflict-suggestion-draggable">',
					'<textarea>b</textarea>',
					'<textarea>c</textarea>',
					'</div></div></div>',
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
					'<div class="mw-twocolconflict-suggestion-draggable">',
					'<textarea>c</textarea>',
					'<textarea>d</textarea>',
					'</div></div></div>',
					'<textarea>e</textarea>',
					'<textarea>f</textarea>',
				]
			],
		];
	}

	/**
	 * @dataProvider provideDiffs
	 */
	public function testGetHtml( array $diff, int $otherIndex, int $yourIndex, array $expected ) {
		$editableTextComponent = $this->createMock( HtmlEditableTextComponent::class );
		$editableTextComponent->method( 'getHtml' )->willReturnCallback( function ( $_, $text ) {
			// We don't care about escaping here, that's tested in HtmlEditableTextComponentTest
			return "<textarea>$text</textarea>";
		} );

		$localizer = $this->createMock( MessageLocalizer::class );
		$localizer->method( 'msg' )->willReturn( $this->createMock( Message::class ) );

		$view = new HtmlTalkPageResolutionView( $editableTextComponent, $localizer );
		$html = $view->getHtml( $diff, $otherIndex, $yourIndex );

		$this->assertStringContainsString( ' name="mw-twocolconflict-single-column-view"', $html,
			'form identifier' );
		$this->assertElementsPresentInOrder( $html, $expected );
	}

	public function assertElementsPresentInOrder( string $html, array $expectedElements ) {
		$offset = 0;
		foreach ( $expectedElements as $element ) {
			$pos = strpos( $html, $element, $offset );
			$this->assertIsInt( $pos, "…$element not found after position " . $offset .
				': …' . substr( $html, $offset, 1000 ) . '…' );
			$offset = $pos + strlen( $element );
		}
	}

}
