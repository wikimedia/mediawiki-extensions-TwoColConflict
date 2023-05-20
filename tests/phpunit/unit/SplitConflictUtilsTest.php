<?php

namespace TwoColConflict\Tests;

use TwoColConflict\SplitConflictUtils;

/**
 * @covers \TwoColConflict\SplitConflictUtils
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SplitConflictUtilsTest extends \MediaWikiUnitTestCase {

	public static function provideSplitText() {
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

	public static function provideLinesToMerge() {
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

	public static function provideLinks() {
		return [
			'regular link' => [
				'<a href="#test">',
				'<a target="_blank" href="#test">',
			],
			'href is not required for performance reasons' => [
				'<a>',
				'<a target="_blank">',
			],
			'when there is already a target' => [
				'<a href="#test" target="_top">',
				'<a href="#test" target="_top">',
			],
		];
	}

	/**
	 * @dataProvider provideLinks
	 */
	public function testAddTargetBlankToLinks( string $html, string $expected ) {
		$this->assertSame( $expected, SplitConflictUtils::addTargetBlankToLinks( $html ) );
	}

}
