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
		$result = SplitConflictMerger::mergeSplitConflictResults(
			[
				[ 'other' => 'A', 'your' => 'B' ],
				[ 'other' => 'C', 'your' => 'D' ],
			],
			[],
			[ 'your', 'other' ]
		);
		$this->assertSame( "B\nC", $result );
	}

	public function testInvalidSideSelection() {
		$result = SplitConflictMerger::mergeSplitConflictResults(
			[
				[ 'other' => 'A', 'your' => 'B' ],
			],
			[],
			[ 1 => 'your' ]
		);
		// FIXME: Is this a good idea? Shouldn't we pick at least one side? But which?
		$this->assertSame( '', $result );
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
