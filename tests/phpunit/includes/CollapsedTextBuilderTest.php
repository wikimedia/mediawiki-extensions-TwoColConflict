<?php

use Wikimedia\TestingAccessWrapper;

/**
 * @covers \CollapsedTextBuilder
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class CollapsedTextBuilderTest extends MediaWikiTestCase {

	public function testbuildCollapsedText_returnFalseWhenInLimit() {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		$this->assertFalse(
			$collapsedTextBuilder->buildCollapsedText( 'One Two Three.', 14 )
		);
		$this->assertFalse(
			$collapsedTextBuilder->buildCollapsedText( 'واحد اثنين ثلاثة', 16 )
		);
	}

	public function testbuildCollapsedText_returnFalseWhenWhenOverLimitWithWhitespaces() {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		$this->assertFalse(
			$collapsedTextBuilder->buildCollapsedText( "One Two Three.\n \n", 14 )
		);
		$this->assertFalse(
			$collapsedTextBuilder->buildCollapsedText( '   واحد اثنين ثلاثة', 16 )
		);
	}

	public function testbuildCollapsedText_cutWhenSingleLineOverLimit() {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		$this->assertEquals(
			'<span class="mw-twocolconflict-diffchange-fadeout-end">One</span>' .
			wfMessage( 'word-separator' ) .
			'<span class="mw-twocolconflict-diffchange-fadeout-start">even.</span>',
			$collapsedTextBuilder->buildCollapsedText( 'One Two Three Four Five Six Seven.', 10 )
		);
	}

	public function testbuildCollapsedText_returnFalseWhenTwoLinesInLimit() {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		$this->assertFalse(
			$collapsedTextBuilder->buildCollapsedText( "One Two\nThree Four.", 25 )
		);
	}

	public function testbuildCollapsedText_cutWhenTwoLinesOverLimit() {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		$this->assertEquals(
			"<span class=\"mw-twocolconflict-diffchange-fadeout-end\">One</span>\n" .
			"<span class=\"mw-twocolconflict-diffchange-fadeout-start\">Four.</span>",
			$collapsedTextBuilder->buildCollapsedText( "One Two\nThree Four.", 10 )
		);
	}

	public function testbuildCollapsedText_cutWhenMultipleLinesInLimit() {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		$this->assertEquals(
			"<span class=\"mw-twocolconflict-diffchange-fadeout-end\">One Two</span>\n" .
			"<span class=\"mw-twocolconflict-diffchange-fadeout-start\">Six Seven.</span>",
			$collapsedTextBuilder->buildCollapsedText( "One Two\nThree Four\nFive Six Seven.", 25 )
		);
	}

	public function testTrimStringToFullWord_noCutWhenInLimit() {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		$this->assertEquals(
			'One Two Three.',
			$collapsedTextBuilder->trimStringToFullWord( 'One Two Three.', 14 )
		);
		$this->assertEquals(
			'واحد اثنين ثلاثة',
			$collapsedTextBuilder->trimStringToFullWord( 'واحد اثنين ثلاثة', 16 )
		);
	}

	public function testTrimStringToFullWord_trimWhiteSpaceAtEndOfResult() {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		$this->assertEquals(
			'One Two',
			$collapsedTextBuilder->trimStringToFullWord( 'One Two Three.', 8, true )
		);
	}

	public function testTrimStringToFullWord_trimWhiteSpaceAtStartOfResult() {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		$this->assertEquals(
			'Three.',
			$collapsedTextBuilder->trimStringToFullWord( 'One Two. And Three.', 7, false )
		);
	}

	/**
	 * @param string $input
	 * @param int $maxLength
	 * @param string $result
	 * @dataProvider provider_trimStringToFullWord_atEnd
	 */
	public function testTrimStringToFullWord_atEnd( $input, $maxLength, $result ) {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		self::assertEquals(
			$result,
			$collapsedTextBuilder->trimStringToFullWord( $input, $maxLength, true )
		);
	}

	public function provider_trimStringToFullWord_atEnd() {
		return [
			[
				'input' => 'One Two Three Four Five Six.',
				'maxLength' => 11,
				'result' => 'One Two',
			],
			[
				'input' => 'Onehundered.',
				'maxLength' => 3,
				'result' => 'One',
			],
			[
				'input' => 'واحد اثنين ثلاثة',
				'maxLength' => 9,
				'result' => 'واحد',
			]
		];
	}

	/**
	 * @param string $input
	 * @param int $maxLength
	 * @param string $result
	 * @dataProvider provider_trimStringToFullWord_atStart
	 */
	public function testTrimStringToFullWord_atStart( $input, $maxLength, $result ) {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		self::assertEquals(
			$result,
			$collapsedTextBuilder->trimStringToFullWord( $input, $maxLength, false )
		);
	}

	public function provider_trimStringToFullWord_atStart() {
		return [
			[
				'input' => 'One Two Three Four Five Six.',
				'maxLength' => 11,
				'result' => 'Five Six.',
			],
			[
				'input' => 'Onehundered.',
				'maxLength' => 3,
				'result' => 'ed.',
			],
			[
				'input' => 'واحد اثنين ثلاثة',
				'maxLength' => 9,
				'result' => 'ثلاثة',
			]
		];
	}

	/**
	 * @param string $input
	 * @param null|boolean $trimAtEnd
	 * @param string $result
	 * @dataProvider provider_trimWhiteSpaces
	 */
	public function testTrimWhiteSpaces( $input, $trimAtEnd, $result ) {
		$collapsedTextBuilder = TestingAccessWrapper::newFromClass( 'CollapsedTextBuilder' );
		self::assertEquals(
			$result,
			$collapsedTextBuilder->trimWhiteSpaces( $input, $trimAtEnd )
		);
	}

	public function provider_trimWhiteSpaces() {
		return [
			[
				'input' => ' Text ',
				'trimAtEnd' => null,
				'result' => 'Text',
			],
			[
				'input' => ' Text ',
				'trimAtEnd' => true,
				'result' => ' Text',
			],
			[
				'input' => ' Text ',
				'trimAtEnd' => false,
				'result' => 'Text ',
			]
		];
	}
}
