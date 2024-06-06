<?php

namespace TwoColConflict\Tests;

use TwoColConflict\SplitConflictMerger;

/**
 * @covers \TwoColConflict\SplitConflictMerger
 *
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class SplitConflictMergerTest extends \PHPUnit\Framework\TestCase {

	public function testSingleCopyRow() {
		$result = ( new SplitConflictMerger() )->mergeSplitConflictResults(
			[
				[ 'copy' => 'A' ],
			],
			[],
			[]
		);
		$this->assertSame( 'A', $result );
	}

	public function testStaticSideSelection() {
		$result = ( new SplitConflictMerger() )->mergeSplitConflictResults(
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

		$result = ( new SplitConflictMerger() )->mergeSplitConflictResults( $rows, [], $sides );
		$this->assertSame( "B\nC", $result );
	}

	public function testInvalidSideSelection() {
		$rows = [
			[ 'other' => 'A', 'your' => 'B' ],
		];
		$sides = [ 1 => 'your' ];

		$result = ( new SplitConflictMerger() )->mergeSplitConflictResults( $rows, [], $sides );
		$this->assertSame( 'B', $result );
	}

	public function testExtraLineFeedsAreAdded() {
		$result = ( new SplitConflictMerger() )->mergeSplitConflictResults(
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

	public function testEmptyLines() {
		$result = ( new SplitConflictMerger() )->mergeSplitConflictResults(
			[
				[ 'copy' => 'A' ],
				// We know this wasn't empty, but is when the form is submitted
				[ 'copy' => '' ],
				[ 'copy' => 'B' ],
				// This was always empty (marked with "was-empty") and is not touched by the user
				[ 'copy' => '' ],
				[ 'copy' => 'C' ],
			],
			[
				1 => [ 'copy' => 2 ],
				3 => [ 'copy' => '0,was-empty' ],
			],
			[]
		);
		// The 2 extra linefeeds are removed, but the empty line between B and C is still there
		$this->assertSame( "A\nB\n\nC", $result );
	}

	public function testLeadingNewlinesNotEmptiedByTheUser() {
		$result = ( new SplitConflictMerger() )->mergeSplitConflictResults(
			[
				[ 'copy' => '' ],
				[ 'copy' => 'A' ],
			],
			[
				[ 'copy' => '1,was-empty' ],
			],
			[]
		);
		$this->assertSame( "\n\nA", $result );
	}

	public function testTrailingNewlinesAreTrimmed() {
		$result = ( new SplitConflictMerger() )->mergeSplitConflictResults(
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
