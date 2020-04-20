<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use Language;
use MediaWikiTestCase;
use Message;
use MessageLocalizer;
use OOUI\BlankTheme;
use OOUI\Theme;
use TwoColConflict\SplitTwoColConflict\HtmlEditableTextComponent;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\HtmlEditableTextComponent
 */
class HtmlEditableTextComponentTest extends MediaWikiTestCase {

	public function setUp() : void {
		parent::setUp();
		Theme::setSingleton( new BlankTheme() );
	}

	public function testEnabledElement() {
		$html = $this->createInstance()->getHtml( '', '', 0, 'copy', false );
		$this->assertStringNotContainsString( 'readonly', $html );
	}

	public function testDisabledElement() {
		$html = $this->createInstance()->getHtml( '', '', 0, 'copy', true );
		$this->assertStringContainsString( 'readonly', $html );
	}

	public function provideExtraLinefeeds() {
		return [
			[ '', '0' ],
			[ 'a', '0' ],
			[ "\n", '1' ],
			[ "a\n", '1' ],
			[ "a\r\n\r\n", '2' ],
			// "Before" and "after" are intentionally flipped, because "before" is very rare
			[ "\na", '0,1' ],
			[ "\r\n\r\na", '0,2' ],
			[ "\r\n\n\n\na\r\n\n\n", '3,4' ],
		];
	}

	/**
	 * @dataProvider provideExtraLinefeeds
	 */
	public function testCountExtraLineFeeds( string $text, string $expected ) {
		/** @var HtmlEditableTextComponent $component */
		$component = TestingAccessWrapper::newFromObject( $this->createInstance() );

		$this->assertSame( $expected, $component->countExtraLineFeeds( $text ) );
	}

	public function provideRowsForText() {
		return [
			[ "a", 3 ],
			[ "a\nb", 3 ],
			[ "a\nb\nc\nd", 4 ],
			[ "01234567890123456789012345678901234567890123456789012345678901234567890123456789"
				. "01234567890123456789012345678901234567890123456789012345678901234567890123456789"
				. "01234567890123456789012345678901234567890123456789012345678901234567890123456789"
				. "01234567890123456789012345678901234567890123456789012345678901234567890123456789",
				6
			],
			[ "㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳"
				. "㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳㠳",
				3
			],
		];
	}

	/**
	 * @dataProvider provideRowsForText
	 */
	public function testRowsForText( string $input, int $rows ) {
		/** @var HtmlEditableTextComponent $component */
		$component = TestingAccessWrapper::newFromObject( $this->createInstance() );

		$this->assertSame( $rows, $component->rowsForText( $input ) );
	}

	private function createInstance() {
		$localizer = $this->createMock( MessageLocalizer::class );
		$localizer->method( 'msg' )->willReturn( $this->createMock( Message::class ) );

		return new HtmlEditableTextComponent(
			$localizer,
			$this->getTestUser()->getUser(),
			$this->createMock( Language::class )
		);
	}

}
