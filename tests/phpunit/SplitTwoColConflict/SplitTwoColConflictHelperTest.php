<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use MediaWikiTestCase;
use OutputPage;
use Title;
use TwoColConflict\SplitTwoColConflict\SplitTwoColConflictHelper;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\SplitTwoColConflictHelper
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SplitTwoColConflictHelperTest extends MediaWikiTestCase {

	public function provideSplitText() {
		return [
			[
				"A\nB\nC",
				[ 'A', 'B', 'C' ],
			],
			[
				"A\r\nB\r\nC",
				[ "A", 'B', 'C' ],
			],
			[
				"A\n\nB\nC",
				[ "A\n", 'B', 'C' ],
			],
			[
				"A\r\n\r\nB\r\nC",
				[ "A\n", 'B', 'C' ],
			],
			[
				"A\n\n\nB\nC",
				[ "A\n\n", 'B', 'C' ],
			],
			[
				"A\r\n\r\n\r\nB\r\nC",
				[ "A\n\n", 'B', 'C' ],
			],
		];
	}

	/**
	 * @param string $input
	 * @param string[] $expectedOutput
	 * @dataProvider provideSplitText
	 */
	public function testSplitText( $input, array $expectedOutput ) {
		$helper = $this->createHelper();

		$this->assertSame(
			$expectedOutput,
			$result = $helper->splitText( $input )
		);
	}

	/**
	 * @return SplitTwoColConflictHelper
	 */
	private function createHelper() {
		$helper = new SplitTwoColConflictHelper(
			Title::newFromText( 'TestTitle' ),
			$this->createMock( OutputPage::class ),
			new \NullStatsdDataFactory(),
			'',
			''
		);

		return TestingAccessWrapper::newFromObject( $helper );
	}

}
