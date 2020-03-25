<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use Content;
use MediaWiki\Revision\RevisionRecord;
use TwoColConflict\SplitTwoColConflict\ResolutionSuggester;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\ResolutionSuggester
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class ResolutionSuggesterTest extends \PHPUnit\Framework\TestCase {

	protected function setUp() : void {
		global $wgTwoColConflictSuggestResolution;

		parent::setUp();

		$wgTwoColConflictSuggestResolution = true;
	}

	public function provideSuggestion() {
		return [
			[
				"",
				"B",
				"C",
				true,
			],
			[
				"",
				"B\nB",
				"C\nC",
				true,
			],
			[
				"A",
				"A\nB",
				"A\nC",
				true,
			],
			[
				"A\n\nA",
				"A\n\nA\nB",
				"A\n\nA\nC",
				true,
			],
			[
				"A",
				"A\nB\nB",
				"A\nC",
				true,
			],
			[
				"A",
				"B\nA",
				"C\nA",
				true,
			],
			[
				"A\nA",
				"A\nB\nA",
				"A\nC\nA",
				true,
			],
			[
				"A\nA",
				"A\nB\nA",
				"A\nA\nC",
				false,
			],
			[
				"A\nA",
				"B\nB\nA",
				"A\nC\nA",
				false,
			],
			[
				"A\nA",
				"A\nB",
				"A\nC\nA",
				false,
			],
			[
				"A",
				"A\nB",
				"C\nC",
				false,
			],
			[
				"A\nA",
				"A\nB\nA\nD",
				"A\nC\nA",
				false,
			],
			[
				"A\nA\nA",
				"A\nA\nD",
				"A\nC\nA",
				false,
			],
			'incompatible 3-row diff' => [
				"A\nB\nC",
				"1\nB\n1",
				"2\nB\n2",
				false,
			],
		];
	}

	/**
	 * @dataProvider provideSuggestion
	 */
	public function testSuggestion(
		string $base,
		string $your,
		string $stored,
		bool $expectedOutput
	) {
		$suggester = $this->createResolutionSuggester( new \WikitextContent( $base ) );

		$this->assertSame(
			$expectedOutput,
			$suggester->getResolutionSuggestion(
				\Title::makeTitle( NS_TALK, __FUNCTION__ ),
				$this->splitText( $stored ),
				$this->splitText( $your )
			)
		);
	}

	public function testGetBaseRevisionLines() {
		$suggester = $this->createResolutionSuggester( new \WikitextContent( "A\nB\nC" ) );
		$this->assertSame( [ 'A','B','C' ], $suggester->getBaseRevisionLines() );
	}

	public function testGetBaseRevisionLinesNoContent() {
		$suggester = $this->createResolutionSuggester( null );
		$this->assertSame( [], $suggester->getBaseRevisionLines() );
	}

	public function testGetBaseRevisionLinesNoBaseRevision() {
		/** @var ResolutionSuggester $suggester */
		$suggester = TestingAccessWrapper::newFromObject(
			new ResolutionSuggester( null, '' )
		);
		$this->assertSame( [], $suggester->getBaseRevisionLines() );
	}

	/**
	 * @param ?Content $content
	 *
	 * @return ResolutionSuggester
	 */
	private function createResolutionSuggester( ?Content $content ) {
		$baseRevisionMock = $this->createMock( RevisionRecord::class );
		$baseRevisionMock->method( 'getContent' )
			->willReturn( $content );
		return TestingAccessWrapper::newFromObject(
			new ResolutionSuggester( $baseRevisionMock, CONTENT_FORMAT_WIKITEXT )
		);
	}

	/**
	 * @param string $text
	 *
	 * @return string[]
	 */
	private function splitText( string $text ) : array {
		return preg_split( '/\n(?!\n)/', $text );
	}

}
