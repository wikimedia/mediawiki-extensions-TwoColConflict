<?php

namespace TwoColConflict\Tests;

use MediaWikiTestCase;
use TwoColConflict\CollapsedTextBuilder;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\CollapsedTextBuilder
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class CollapsedTextBuilderTest extends MediaWikiTestCase {

	public function testBuildCollapsedText_returnFalseWhenInLimit() {
		$collapsedTextBuilder = new CollapsedTextBuilder();
		$this->assertFalse(
			$collapsedTextBuilder->buildCollapsedText( 'One Two Three.', 14 )
		);
		$this->assertFalse(
			$collapsedTextBuilder->buildCollapsedText( 'واحد اثنين ثلاثة', 16 )
		);
	}

	public function testBuildCollapsedText_returnFalseWhenWhenOverLimitWithWhitespaces() {
		$collapsedTextBuilder = new CollapsedTextBuilder();
		$this->assertFalse(
			$collapsedTextBuilder->buildCollapsedText( "One Two Three.\n \n", 14 )
		);
		$this->assertFalse(
			$collapsedTextBuilder->buildCollapsedText( '   واحد اثنين ثلاثة', 16 )
		);
	}

	public function testBuildCollapsedText_cutWhenSingleLineOverLimit() {
		$collapsedTextBuilder = new CollapsedTextBuilder();
		$this->assertSame(
			'<span class="mw-twocolconflict-diffchange-fadeout-end">One</span>' .
			wfMessage( 'word-separator' ) .
			'<span class="mw-twocolconflict-diffchange-fadeout-start">even.</span>',
			$collapsedTextBuilder->buildCollapsedText( 'One Two Three Four Five Six Seven.', 10 )
		);
	}

	public function testBuildCollapsedText_returnFalseWhenTwoLinesInLimit() {
		$collapsedTextBuilder = new CollapsedTextBuilder();
		$this->assertFalse(
			$collapsedTextBuilder->buildCollapsedText( "One Two\nThree Four.", 25 )
		);
	}

	public function testBuildCollapsedText_cutWhenTwoLinesOverLimit() {
		$collapsedTextBuilder = new CollapsedTextBuilder();
		$this->assertSame(
			"<span class=\"mw-twocolconflict-diffchange-fadeout-end\">One</span>\n" .
			"<span class=\"mw-twocolconflict-diffchange-fadeout-start\">Four.</span>",
			$collapsedTextBuilder->buildCollapsedText( "One Two\nThree Four.", 10 )
		);
	}

	public function testBuildCollapsedText_cutWhenMultipleLinesInLimit() {
		$collapsedTextBuilder = new CollapsedTextBuilder();
		$this->assertSame(
			"<span class=\"mw-twocolconflict-diffchange-fadeout-end\">One Two</span>\n" .
			"<span class=\"mw-twocolconflict-diffchange-fadeout-start\">Six Seven.</span>",
			$collapsedTextBuilder->buildCollapsedText( "One Two\nThree Four\nFive Six Seven.", 25 )
		);
	}

	public function testTrimStringToFullWord_noCutWhenInLimit() {
		$collapsedTextBuilder = $this->newInstance();
		$this->assertSame(
			'One Two Three.',
			$collapsedTextBuilder->trimStringToFullWord( 'One Two Three.', 14 )
		);
		$this->assertSame(
			'واحد اثنين ثلاثة',
			$collapsedTextBuilder->trimStringToFullWord( 'واحد اثنين ثلاثة', 16 )
		);
	}

	public function testTrimStringToFullWord_trimWhiteSpaceAtEndOfResult() {
		$collapsedTextBuilder = $this->newInstance();
		$this->assertSame(
			'One Two',
			$collapsedTextBuilder->trimStringToFullWord( 'One Two Three.', 8, true )
		);
	}

	public function testTrimStringToFullWord_trimWhiteSpaceAtStartOfResult() {
		$collapsedTextBuilder = $this->newInstance();
		$this->assertSame(
			'Three.',
			$collapsedTextBuilder->trimStringToFullWord( 'One Two. And Three.', 7, false )
		);
	}

	/**
	 * @param string $input
	 * @param int $maxLength
	 * @param string $result
	 * @dataProvider provideTrimStringToFullWord_atEnd
	 */
	public function testTrimStringToFullWord_atEnd( $input, $maxLength, $result ) {
		$collapsedTextBuilder = $this->newInstance();
		$this->assertSame(
			$result,
			$collapsedTextBuilder->trimStringToFullWord( $input, $maxLength, true )
		);
	}

	public function provideTrimStringToFullWord_atEnd() {
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
	 * @dataProvider provideTrimStringToFullWord_atStart
	 */
	public function testTrimStringToFullWord_atStart( $input, $maxLength, $result ) {
		$collapsedTextBuilder = $this->newInstance();
		$this->assertSame(
			$result,
			$collapsedTextBuilder->trimStringToFullWord( $input, $maxLength, false )
		);
	}

	public function provideTrimStringToFullWord_atStart() {
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
	 * @dataProvider provideTrimWhiteSpaces
	 */
	public function testTrimWhiteSpaces( $input, $trimAtEnd, $result ) {
		$collapsedTextBuilder = $this->newInstance();
		$this->assertSame(
			$result,
			$collapsedTextBuilder->trimWhiteSpaces( $input, $trimAtEnd )
		);
	}

	public function provideTrimWhiteSpaces() {
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

	/**
	 * @return CollapsedTextBuilder
	 */
	private function newInstance() {
		return TestingAccessWrapper::newFromClass( CollapsedTextBuilder::class );
	}

}
