<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use TwoColConflict\SplitTwoColConflict\SplitConflictMerger;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\SplitConflictMerger
 *
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class SplitConflictMergerTest extends \PHPUnit\Framework\TestCase {

	public function testSingleCopyRow() {
		$result = SplitConflictMerger::mergeSplitConflictResults(
			[
				[ 'copy' => 'A' ],
			],
			[],
			[]
		);
		$this->assertSame( 'A', $result );
	}

	public function testStaticSideSelection() {
		$result = SplitConflictMerger::mergeSplitConflictResults(
			[
				[ 'other' => 'A', 'your' => 'B' ],
			],
			[],
			'your'
		);
		$this->assertSame( 'B', $result );
	}

	public function testMixedSideSelection() {
		$rows = [
			[ 'other' => 'A', 'your' => 'B' ],
			[ 'other' => 'C', 'your' => 'D' ],
		];
		$sides = [ 'your', 'other' ];

		$this->assertTrue( SplitConflictMerger::validateSideSelection( $rows, $sides ) );
		$result = SplitConflictMerger::mergeSplitConflictResults( $rows, [], $sides );
		$this->assertSame( "B\nC", $result );
	}

	public function testInvalidSideSelection() {
		$rows = [
			[ 'other' => 'A', 'your' => 'B' ],
		];
		$sides = [ 1 => 'your' ];

		$this->assertFalse( SplitConflictMerger::validateSideSelection( $rows, $sides ) );
		$result = SplitConflictMerger::mergeSplitConflictResults( $rows, [], $sides );
		$this->assertSame( 'B', $result );
	}

	public function testExtraLineFeedsAreAdded() {
		$result = SplitConflictMerger::mergeSplitConflictResults(
			[
				[ 'copy' => 'A' ],
				[ 'copy' => 'B' ],
			],
			[
				[ 'copy' => 2 ],
			],
			[]
		);
		$this->assertSame( "A\n\n\nB", $result );
	}

	public function testEmptyLinesAreSkipped() {
		$result = SplitConflictMerger::mergeSplitConflictResults(
			[
				[ 'copy' => 'A' ],
				[ 'copy' => '' ],
				[ 'copy' => 'B' ],
			],
			[
				1 => [ 'copy' => 2 ],
			],
			[]
		);
		$this->assertSame( "A\nB", $result );
	}

}
