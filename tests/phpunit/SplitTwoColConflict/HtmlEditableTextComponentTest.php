<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use MediaWikiTestCase;
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
		$html = ( new HtmlEditableTextComponent(
			$this->getTestUser()->getUser(),
			new \Language()
		) )->getHtml( '', '', 0, 'copy', false );
		$this->assertStringNotContainsString( 'readonly', $html );
	}

	public function testDisabledElement() {
		$html = ( new HtmlEditableTextComponent(
			$this->getTestUser()->getUser(),
			new \Language()
		) )->getHtml( '', '', 0, 'copy', true );
		$this->assertStringContainsString( 'readonly', $html );
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
	public function testRowsForText( $input, $rows ) {
		$component = TestingAccessWrapper::newFromObject(
			new HtmlEditableTextComponent(
				$this->getTestUser()->getUser(),
				new \Language()
			)
		);

		$this->assertSame( $rows, $component->rowsForText( $input ) );
	}

}
