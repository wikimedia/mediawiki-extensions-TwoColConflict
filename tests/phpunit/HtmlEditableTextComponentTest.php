<?php

namespace TwoColConflict\Tests;

use MediaWiki\Language\Language;
use MediaWiki\Language\RawMessage;
use MediaWiki\Output\OutputPage;
use MediaWikiIntegrationTestCase;
use MessageLocalizer;
use TwoColConflict\Html\HtmlEditableTextComponent;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\Html\HtmlEditableTextComponent
 *
 * @license GPL-2.0-or-later
 */
class HtmlEditableTextComponentTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		// intentionally not reset in teardown, see Icb6901f4d5
		OutputPage::setupOOUI();
	}

	public function testEnabledElement() {
		$html = $this->createInstance()->getHtml( '', '', 0, '' );
		$this->assertStringNotContainsString( 'readonly', $html );
	}

	public function testDisabledElement() {
		$html = $this->createInstance()->getHtml( '', '', 0, '', true );
		$this->assertStringContainsString( 'readonly', $html );
	}

	public static function provideEditorTexts() {
		return [
			'escaping' => [ '<script>alert()</script>', "&lt;script>alert()&lt;/script>\n", '0' ],

			// Newlines are trimmed and one enforced
			'single line' => [ 'a', "a\n", '0' ],
			'two lines' => [ "a\nb", "a\nb\n", '0' ],
			'leading newlines' => [ "\n\na", "a\n", '0,2' ],
			'trailing newlines' => [ "a\n\n", "a\n", '2' ],

			// But no enforced newline when empty
			'empty' => [ '', '', '0,was-empty' ],
			'no text' => [ null, '', '0' ],
			'newline only' => [ "\n", '', '1,was-empty' ],
		];
	}

	/**
	 * @dataProvider provideEditorTexts
	 */
	public function testGetHtml( ?string $text, string $expectedText, string $expectedLinefeeds ) {
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
		$name = strpos( $html, ' name="mw-twocolconflict-split-linefeeds[0][&lt;TYPE&gt;]"' );
		$value = strpos( $html, " value=\"$expectedLinefeeds\"" );
		if ( !$expectedLinefeeds ) {
			$this->assertFalse( $name, 'linefeed tracking element name' );
			$this->assertFalse( $value, 'linefeed tracking value' );
		} else {
			$this->assertIsInt( $name, 'linefeed tracking element name' );
			$this->assertIsInt( $value, 'linefeed tracking value' );
		}
		$this->assertStringContainsString( ">$expectedText</textarea>", $html );
	}

	public static function provideRawTextareaContents() {
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

	public static function provideExtraLinefeeds() {
		return [
			[ null, '0' ],
			[ '', '0,was-empty' ],
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
	public function testCountExtraLineFeeds( ?string $text, string $expected ) {
		/** @var HtmlEditableTextComponent $component */
		$component = TestingAccessWrapper::newFromObject( $this->createInstance() );

		$this->assertSame( $expected, $component->countExtraLineFeeds( $text ) );
	}

	public static function provideRowsForText() {
		return [
			[ "a", 3 ],
			[ "a\nb", 3 ],
			[ "a\nb\nc\nd", 4 ],
			[ str_repeat( "a\n", 19 ), 20 ],
			[ str_repeat( "a\n", 100 ), 36 ],
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
		$localizer = new class implements MessageLocalizer {
			public function msg( $key, ...$params ) {
				return new RawMessage( '' );
			}
		};

		return new HtmlEditableTextComponent(
			$localizer,
			$this->createMock( Language::class )
		);
	}

}
