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
		$html = $this->createInstance()->getHtml( '', '', 0, '' );
		$this->assertStringNotContainsString( 'readonly', $html );
	}

	public function testDisabledElement() {
		$html = $this->createInstance()->getHtml( '', '', 0, '', true );
		$this->assertStringContainsString( 'readonly', $html );
	}

	public function provideEditorTexts() {
		return [
			'escaping' => [ '<script>alert()</script>', "&lt;script>alert()&lt;/script>\n", '0' ],

			// Newlines are trimmed and one enforced
			'single line' => [ 'a', "a\n", '0' ],
			'two lines' => [ "a\nb", "a\nb\n", '0' ],
			'leading newlines' => [ "\n\na", "a\n", '0,2' ],
			'trailing newlines' => [ "a\n\n", "a\n", '2' ],

			// But no enforced newline when empty
			'empty' => [ '', '', '0' ],
			'newline only' => [ "\n", '', '1,was-empty' ],
		];
	}

	/**
	 * @dataProvider provideEditorTexts
	 */
	public function testGetHtml( string $text, string $expectedText, string $expectedLinefeeds ) {
		$component = $this->createInstance();
		$html = $component->getHtml( '<span>DIFF</span>', $text, 0, '<TYPE>' );

		$this->assertStringContainsString(
			'<span class="mw-twocolconflict-split-reset-diff-text"><span>DIFF</span></span>',
			$html,
			'diff element'
		);
		$this->assertStringContainsString(
			' name="mw-twocolconflict-split-content[0][&lt;TYPE&gt;]"',
			$html,
			'content element name'
		);
		$this->assertStringContainsString(
			' name="mw-twocolconflict-split-linefeeds[0][&lt;TYPE&gt;]"',
			$html,
			'linefeed tracking element name'
		);
		$this->assertStringContainsString(
			" value=\"$expectedLinefeeds\"",
			$html,
			'linefeed tracking value'
		);
		$this->assertStringContainsString( ">$expectedText</textarea>", $html );
	}

	public function provideRawTextareaContents() {
		return [
			// A leading newline needs to be duplicated
			'newline only' => [ "\n", "\n\n" ],
			'leading newlines' => [ "\n\na", "\n\n\na" ],

			// Anything else should not be touched
			'empty' => [ '', '' ],
			'single line' => [ 'a', 'a' ],
			'two lines' => [ "a\nb", "a\nb" ],
			'trailing newlines' => [ "a\n\n", "a\n\n" ],
		];
	}

	/**
	 * @dataProvider provideRawTextareaContents
	 */
	public function testRawTextareaContents( string $text, string $expected ) {
		/** @var HtmlEditableTextComponent $component */
		$component = TestingAccessWrapper::newFromObject( $this->createInstance() );
		$html = $component->buildTextEditor( $text, 0, '', false );

		$this->assertStringContainsString( ">$expected</textarea>", $html );
	}

	public function provideExtraLinefeeds() {
		return [
			[ '', '0' ],
			[ 'a', '0' ],
			[ "\n", '1,was-empty' ],
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
