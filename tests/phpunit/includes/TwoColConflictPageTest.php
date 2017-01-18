<?php

/**
 * @license GNU GPL v2+
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictPageTest extends MediaWikiTestCase {

	/**
	 * @covers TwoColConflictPageTest::getCollapsedText
	 */
	public function testGetCollapsedText_returnFalseWhenInLimit() {
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $this->getMockPage() );
		$this->assertFalse(
			$twoColConflictPageMock->getCollapsedText( 'One Two Three.', 14 )
		);
		$this->assertFalse(
			$twoColConflictPageMock->getCollapsedText( 'واحد اثنين ثلاثة', 16 )
		);
	}

	/**
	 * @covers TwoColConflictPageTest::getCollapsedText
	 */
	public function testGetCollapsedText_returnFalseWhenWhenOverLimitWithWhitespaces() {
		$twoColConflictPageMock = $this->getMockPageWithContext();
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $twoColConflictPageMock );
		$this->assertFalse(
			$twoColConflictPageMock->getCollapsedText( "One Two Three.\n \n", 14 )
		);
		$this->assertFalse(
			$twoColConflictPageMock->getCollapsedText( '   واحد اثنين ثلاثة', 16 )
		);
	}

	/**
	 * @covers TwoColConflictPageTest::getCollapsedText
	 */
	public function testGetCollapsedText_cutWhenSingleLineOverLimit() {
		$twoColConflictPageMock = $this->getMockPageWithContext();
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $twoColConflictPageMock );
		$this->assertEquals(
			'<span class="mw-twocolconflict-diffchange-fadeout-end">One</span> ' .
			'<span class="mw-twocolconflict-diffchange-fadeout-start">even.</span>',
			$twoColConflictPageMock->getCollapsedText( 'One Two Three Four Five Six Seven.', 10 )
		);
	}

	/**
	 * @covers TwoColConflictPageTest::getCollapsedText
	 */
	public function testGetCollapsedText_returnFalseWhenTwoLinesInLimit() {
		$twoColConflictPageMock = $this->getMockPageWithContext();
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $twoColConflictPageMock );
		$this->assertFalse(
			$twoColConflictPageMock->getCollapsedText( "One Two\nThree Four.", 25 )
		);
	}

	/**
	 * @covers TwoColConflictPageTest::getCollapsedText
	 */
	public function testGetCollapsedText_cutWhenTwoLinesOverLimit() {
		$twoColConflictPageMock = $this->getMockPageWithContext();
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $twoColConflictPageMock );
		$this->assertEquals(
			"<span class=\"mw-twocolconflict-diffchange-fadeout-end\">One</span>\n" .
			"<span class=\"mw-twocolconflict-diffchange-fadeout-start\">Four.</span>",
			$twoColConflictPageMock->getCollapsedText( "One Two\nThree Four.", 10 )
		);
	}

	/**
	 * @covers TwoColConflictPageTest::getCollapsedText
	 */
	public function testGetCollapsedText_cutWhenMultipleLinesInLimit() {
		$twoColConflictPageMock = $this->getMockPageWithContext();
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $twoColConflictPageMock );
		$this->assertEquals(
			"<span class=\"mw-twocolconflict-diffchange-fadeout-end\">One Two</span>\n" .
			"<span class=\"mw-twocolconflict-diffchange-fadeout-start\">Six Seven.</span>",
			$twoColConflictPageMock->getCollapsedText( "One Two\nThree Four\nFive Six Seven.", 25 )
		);
	}

	/**
	 * @covers TwoColConflictPageTest::trimStringToFullWord
	 */
	public function testTrimStringToFullWord_noCutWhenInLimit() {
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $this->getMockPage() );
		$this->assertEquals(
			'One Two Three.',
			$twoColConflictPageMock->trimStringToFullWord( 'One Two Three.', 14 )
		);
		$this->assertEquals(
			'واحد اثنين ثلاثة',
			$twoColConflictPageMock->trimStringToFullWord( 'واحد اثنين ثلاثة', 16 )
		);
	}

	/**
	 * @covers TwoColConflictPageTest::trimStringToFullWord
	 */
	public function testTrimStringToFullWord_trimWhiteSpaceAtEndOfResult() {
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $this->getMockPage() );
		$this->assertEquals(
			'One Two',
			$twoColConflictPageMock->trimStringToFullWord( 'One Two Three.', 8, true )
		);
	}

	/**
	 * @covers TwoColConflictPageTest::trimStringToFullWord
	 */
	public function testTrimStringToFullWord_trimWhiteSpaceAtStartOfResult() {
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $this->getMockPage() );
		$this->assertEquals(
			'Three.',
			$twoColConflictPageMock->trimStringToFullWord( 'One Two. And Three.', 7, false )
		);
	}

	/**
	 * @param string $input
	 * @param int $maxLength
	 * @param string $result
	 * @dataProvider provider_trimStringToFullWord_atEnd
	 * @covers TwoColConflictPageTest::trimStringToFullWord
	 */
	public function testTrimStringToFullWord_atEnd( $input, $maxLength, $result ) {
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $this->getMockPage() );
		self::assertEquals(
			$result,
			$twoColConflictPageMock->trimStringToFullWord( $input, $maxLength, true )
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
	 * @covers TwoColConflictPageTest::trimStringToFullWord
	 */
	public function testTrimStringToFullWord_atStart( $input, $maxLength, $result ) {
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $this->getMockPage() );
		self::assertEquals(
			$result,
			$twoColConflictPageMock->trimStringToFullWord( $input, $maxLength, false )
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
	 * @covers TwoColConflictPageTest::trimWhiteSpaces
	 */
	public function testTrimWhiteSpaces( $input, $trimAtEnd, $result ) {
		$twoColConflictPageMock = TestingAccessWrapper::newFromObject( $this->getMockPage() );
		self::assertEquals(
			$result,
			$twoColConflictPageMock->trimWhiteSpaces( $input, $trimAtEnd )
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

	private function getMockPage() {
		return $this->getMockBuilder( 'TwoColConflictPage' )
			->disableOriginalConstructor()
			->getMock();
	}

	private function getMockPageWithContext() {
		$mockContext = $this->getMockBuilder( 'RequestContext' )
			->disableOriginalConstructor()
			->getMock();
		$mockContext->method( 'msg' )
			->will( $this->returnValueMap( [
				[ 'word-separator', ' ' ]
			] ) );

		$twoColConflictPageMock = $this->getMockPage();
		$twoColConflictPageMock->method( 'getContext' )
			->will( $this->returnValue( $mockContext ) );

		return $twoColConflictPageMock;
	}
}
