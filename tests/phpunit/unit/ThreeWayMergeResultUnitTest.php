<?php

namespace TwoColConflict\Tests;

use MediaWikiUnitTestCase;
use TwoColConflict\Logging\ThreeWayMergeResult;

/**
 * @covers \TwoColConflict\Logging\ThreeWayMergeResult
 */
class ThreeWayMergeResultUnitTest extends MediaWikiUnitTestCase {

	/**
	 * @dataProvider provideOverlappingChunks
	 */
	public function testGetters(
		string $mergeLeftovers,
		int $expectedCount,
		int $expectedSize
	) {
		$result = new ThreeWayMergeResult( false, '', $mergeLeftovers );
		$this->assertFalse( $result->isCleanMerge() );
		$this->assertSame( $expectedCount, $result->getOverlappingChunkCount(), 'count' );
		$this->assertSame( $expectedSize, $result->getOverlappingChunkSize(), 'size' );
	}

	public function provideOverlappingChunks() {
		return [
			[
				'mergeLeftovers' => '',
				'expectedCount' => 0,
				'expectedSize' => 0,
			],
			[
				'mergeLeftovers' => "10a\nfoo\nbar\n.\n",
				'expectedCount' => 1,
				'expectedSize' => 7,
			],
			[
				'mergeLeftovers' => "10a\nbar\n.\n20a\nbar\n\n.\n",
				'expectedCount' => 2,
				'expectedSize' => 7,
			],
			[
				'mergeLeftovers' => "1,3d\n",
				'expectedCount' => 1,
				'expectedSize' => 0,
			],
			[
				'mergeLeftovers' => "1,3d\n5,7d\n",
				'expectedCount' => 2,
				'expectedSize' => 0,
			],
			[
				'mergeLeftovers' => "1,3c\nfoo\nbar\n.\n5,7d\n9,10a\nmore\n.\n",
				'expectedCount' => 3,
				'expectedSize' => 11,
			],
		];
	}

}
