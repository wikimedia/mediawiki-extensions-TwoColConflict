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

		$result = SplitConflictMerger::mergeSplitConflictResults( $rows, [], $sides );
		$this->assertSame( "B\nC", $result );
	}

	public function testInvalidSideSelection() {
		$rows = [
			[ 'other' => 'A', 'your' => 'B' ],
		];
		$sides = [ 1 => 'your' ];

		$result = SplitConflictMerger::mergeSplitConflictResults( $rows, [], $sides );
		$this->assertSame( 'B', $result );
	}

	// TODO: public function testTalkPageSpecialCase()

	public function testExtraLineFeedsAreAdded() {
		$result = SplitConflictMerger::mergeSplitConflictResults(
			[
				[ 'copy' => 'A' ],
				[ 'copy' => 'B' ],
			],
			[
				[ 'copy' => '2,1' ],
			],
			[]
		);
		$this->assertSame( "\nA\n\n\nB", $result );
	}

	public function testEmptyLinesAreSkipped() {
		$result = SplitConflictMerger::mergeSplitConflictResults(
			[
				[ 'copy' => 'A' ],
				// We assume the user intentionally emptied this
				[ 'copy' => '' ],
				[ 'copy' => 'B' ],
			],
			[
				// The tracked linefeeds should be removed with the text
				1 => [ 'copy' => 2 ],
			],
			[]
		);
		$this->assertSame( "A\nB", $result );
	}

	public function testFirstEmptyRowIsNotIgnored() {
		$result = SplitConflictMerger::mergeSplitConflictResults(
			[
				[ 'copy' => '' ],
				[ 'copy' => 'A' ],
			],
			[
				[ 'copy' => 1 ],
			],
			[]
		);
		$this->assertSame( "\n\nA", $result );
	}

	public function testTrailingNewlinesAreTrimmed() {
		$result = SplitConflictMerger::mergeSplitConflictResults(
			[
				[ 'copy' => "A\n\n" ],
				[ 'copy' => 'B' ],
			],
			[],
			[]
		);
		$this->assertSame( "A\nB", $result );
	}

}
