<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use MediaWikiTestCase;
use TwoColConflict\SplitTwoColConflict\SplitConflictUtils;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\SplitConflictUtils
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SplitConflictUtilsTest extends MediaWikiTestCase {

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
	 * @param string $input
	 * @param string[] $expectedOutput
	 * @dataProvider provideSplitText
	 */
	public function testSplitText( string $input, array $expectedOutput ) {
		$this->assertSame(
			$expectedOutput,
			$result = SplitConflictUtils::splitText( $input )
		);
	}
}
