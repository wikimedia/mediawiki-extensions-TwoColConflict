<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

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
		];
	}

	/**
	 * @param string $base
	 * @param string $your
	 * @param string $stored
	 * @param bool $expectedOutput
	 * @dataProvider provideSuggestion
	 */
	public function testSuggestion( $base, $your, $stored, $expectedOutput ) {
		$suggester = $this->createResolutionSuggester( new \WikitextContent( $base ) );

		$this->assertSame(
			$expectedOutput,
			$suggester->getResolutionSuggestion(
				$this->splitText( $your ),
				$this->splitText( $stored )
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
		$suggester = TestingAccessWrapper::newFromObject(
			new ResolutionSuggester( null, '' )
		);
		$this->assertSame( [], $suggester->getBaseRevisionLines() );
	}

	/**
	 * @param \Content $content
	 *
	 * @return ResolutionSuggester
	 */
	private function createResolutionSuggester( $content ) {
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
	private function splitText( $text ) {
		return preg_split( '/\n(?!\n)/', $text );
	}

}
