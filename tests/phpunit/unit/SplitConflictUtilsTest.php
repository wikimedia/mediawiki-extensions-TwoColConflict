<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use TwoColConflict\SplitTwoColConflict\SplitConflictUtils;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\SplitConflictUtils
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SplitConflictUtilsTest extends \MediaWikiUnitTestCase {

	public function provideSplitText() {
		return [
			[
				"A\nB\nC",
				[ 'A', 'B', 'C' ],
			],
			[
				"A\r\nB\r\nC",
				[ 'A', 'B', 'C' ],
			],
			[
				"A\n\nB\nC",
				[ 'A', '', 'B', 'C' ],
			],
			[
				"A\r\n\r\nB\r\nC",
				[ 'A', '', 'B', 'C' ],
			],
			[
				"A\n\n\nB\nC",
				[ 'A', '', '', 'B', 'C' ],
			],
			[
				"A\r\n\r\n\r\nB\r\nC",
				[ 'A', '', '', 'B', 'C' ],
			],
		];
	}

	/**
	 * @dataProvider provideSplitText
	 */
	public function testSplitText( string $text, array $expected ) {
		$this->assertSame( $expected, SplitConflictUtils::splitText( $text ) );
	}

	public function provideLinesToMerge() {
		return [
			'empty' => [
				[],
				''
			],
			'simple' => [
				[ 'a', 'b' ],
				"a\nb"
			],
			'accept extra line endings' => [
				[ "a\n", "b\n" ],
				"a\n\nb\n"
			],
			'normalize line endings' => [
				[ "a\r", "b\r\n", "c" ],
				"a\n\nb\n\nc"
			],
		];
	}

	/**
	 * @dataProvider provideLinesToMerge
	 */
	public function testMergeTextLines( array $lines, string $expected ) {
		$this->assertSame( $expected, SplitConflictUtils::mergeTextLines( $lines ) );
	}

}
