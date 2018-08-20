<?php

namespace TwoColConflict\Tests;

use Diff;
use MediaWikiTestCase;
use TwoColConflict\LineBasedUnifiedDiffFormatter;

/**
 * @covers \TwoColConflict\LineBasedUnifiedDiffFormatter
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class LineBasedUnifiedDiffFormatterTest extends MediaWikiTestCase {

	/**
	 * @param string $before
	 * @param string $after
	 * @param array[] $expectedOutput
	 * @dataProvider provideFormat
	 */
	public function testFormat( $before, $after, array $expectedOutput ) {
		$diff = new Diff( $this->splitText( $before ), $this->splitText( $after ) );
		$instance = new LineBasedUnifiedDiffFormatter();
		$output = $instance->format( $diff );
		$this->assertArrayEquals( $expectedOutput, $output );
	}

	public function provideFormat() {
		return [
			[
				'before' => 'Just text.',
				'after' => 'Just text.',
				'result' => [
					0 => [
						[
							'action' => 'copy',
							'copy' => 'Just text.',
							'oldline' => 0,
							'newline' => 0,
						]
					]
				],
			],
			[
				'before' => 'Just text.',
				'after' => 'Just text. And more.',
				'result' => [
					0 => [
						[
							'action' => 'delete',
							'old' => 'Just text.',
							'oldline' => 0,
							'count' => 1,
						],
						[
							'action' => 'add',
							'new' => 'Just text<ins class="diffchange">. And more</ins>.',
							'newline' => 0,
							'count' => 1,
						]
					],
				],
			],
			[
				'before' => 'Just less text.',
				'after' => 'Just less.',
				'result' => [
					0 => [
						[
							'action' => 'delete',
							'old' => 'Just less <del class="diffchange">text</del>.',
							'oldline' => 0,
							'count' => 1,
						],
						[
							'action' => 'add',
							'new' => 'Just less.',
							'newline' => 0,
							'count' => 1,
						]
					]
				],
			],
			[
				'before' => <<<TEXT
Just multi-line text.
Line number 2.
TEXT
				,
				'after' => <<<TEXT
Just multi-line text.
Line number 1.5.
Line number 2.
TEXT
				,
				'result' => [
					0 => [
						[
							'action' => 'copy',
							'copy' => 'Just multi-line text.',
							'oldline' => 0,
							'newline' => 0,
						],
					],
					1 => [
						[
							'action' => 'add',
							'new' => '<ins class="diffchange">Line number 1.5.</ins>',
							'newline' => 1,
							'count' => 1,
						],
						[
							'action' => 'copy',
							'copy' => 'Line number 2.',
							'oldline' => 1,
							'newline' => 2,
						]
					]
				],
			],
			[
				'before' => <<<TEXT
Delete the empty line below.

TEXT
				,
				'after' => <<<TEXT
Delete the empty line below.
TEXT
				,
				'result' => [
					0 => [
						[
							'action' => 'copy',
							'copy' => 'Delete the empty line below.',
							'oldline' => 0,
							'newline' => 0,
						],
					],
					1 => [
						[
							'action' => 'delete',
							'old' => "<del class=\"diffchange\">\u{00A0}</del>",
							'oldline' => 1,
							'count' => 1,
						],
					],
				],
			],
			[
				'before' => <<<TEXT
Add an empty line below.
TEXT
				,
				'after' => <<<TEXT
Add an empty line below.

TEXT
				,
				'result' => [
					0 => [
						[
							'action' => 'copy',
							'copy' => 'Add an empty line below.',
							'oldline' => 0,
							'newline' => 0,
						],
					],
					1 => [
						[
							'action' => 'add',
							'new' => "<ins class=\"diffchange\">\u{00A0}</ins>",
							'newline' => 1,
							'count' => 1,
						],
					],
				],
			],
			[
				'before' => <<<TEXT
Just multi-line text.
Line number 1.5.
Line number 2.
TEXT
				,
				'after' => <<<TEXT
Just multi-line text.
Line number 1.5.
TEXT
				,
				'result' => [
					0 => [
						[
							'action' => 'copy',
							'copy' => "Just multi-line text.\nLine number 1.5.",
							'oldline' => 0,
							'newline' => 0,
						],
					],
					1 => [
						[
							'action' => 'delete',
							'old' => '<del class="diffchange">Line number 2.</del>',
							'oldline' => 2,
							'count' => 1,
						]
					]
				],
			],
			[
				'before' => <<<TEXT
Just multi-line text.
Line number 1.5.
Line number 2.
TEXT
				,
				'after' => <<<TEXT
Just multi-line test.
Line number 2.
Line number 3.
TEXT
				,
				'result' => [
					0 => [
						[
							'action' => 'delete',
							'old' => <<<TEXT
Just multi-line <del class="diffchange">text.</del>
<del class="diffchange">Line number 1.5</del>.
TEXT
							,
							'oldline' => 0,
							'count' => 2,
						],
						[
							'action' => 'add',
							'new' => 'Just multi-line <ins class="diffchange">test</ins>.',
							'newline' => 0,
							'count' => 1,
						]
					],
					2 => [
						[
							'action' => 'copy',
							'copy' => 'Line number 2.',
							'oldline' => 2,
							'newline' => 1,
						],
					],
					3 => [
						[
							'action' => 'add',
							'new' => '<ins class="diffchange">Line number 3.</ins>',
							'newline' => 2,
							'count' => 1,
						]
					],
				],
			],
			[
				'before' => <<<TEXT
Just multi-line text.
To change number 2.
To change number 3.
TEXT
				,
				'after' => <<<TEXT
Just multi-line test.
Line number 2 changed.
Line number 3 also changed.
TEXT
				,
				'result' => [
					0 => [
						[
							'action' => 'delete',
							'old' => <<<TEXT
Just multi-line <del class="diffchange">text</del>.
<del class="diffchange">To change </del>number 2.
<del class="diffchange">To change </del>number 3.
TEXT
							,
							'oldline' => 0,
							'count' => 3,
						],
						[
							'action' => 'add',
							'new' => <<<TEXT
Just multi-line <ins class="diffchange">test</ins>.
<ins class="diffchange">Line </ins>number 2 <ins class="diffchange">changed</ins>.
<ins class="diffchange">Line </ins>number 3 <ins class="diffchange">also changed</ins>.
TEXT
							,
							'newline' => 0,
							'count' => 3,
						]
					],
				],
			],
			[
				'before' => <<<TEXT
Just a multi-line text.
Line number two. This line is quite long!
And that's line number three - even longer than the line before.

Just another line with an empty line above.
TEXT
				,
				'after' => <<<TEXT
Just a multi-line text.
Add something new.
Line number two. Now line number three and quite long!
Add more new stuff.
TEXT
				,
				'result' => [
					0 => [
						[
							'action' => 'copy',
							'copy' => 'Just a multi-line text.',
							'oldline' => 0,
							'newline' => 0,
						],
					],
					1 => [
						[
							'action' => 'delete',
							'old' =>
// @codingStandardsIgnoreStart
<<<TEXT
Line number two. <del class="diffchange">This </del>line <del class="diffchange">is </del>quite long!
<del class="diffchange">And that's line number three - even longer than the line before.</del>
\u{00A0}
<del class="diffchange">Just another line with an empty line above</del>.
TEXT
// @codingStandardsIgnoreEnd
							,
							'oldline' => 1,
							'count' => 3,
						],
						[
							'action' => 'add',
							'new' =>
// @codingStandardsIgnoreStart
<<<TEXT
<ins class="diffchange">Add something new.</ins>
Line number two. <ins class="diffchange">Now </ins>line <ins class="diffchange">number three and </ins>quite long!
<ins class="diffchange">Add more new stuff</ins>.
TEXT
// @codingStandardsIgnoreEnd
							,
							'newline' => 1,
							'count' => 3,
						]
					],
				],
			],
			[
				'before' => <<<TEXT
Just a multi-line text.
Line number two. This line is quite long!
Line number three.
TEXT
				,
				'after' => <<<TEXT
Just a multi-line text.
Line number two. This line is now a bit longer!

And it gets even longer.

Line number three.
TEXT
				,
				'result' => [
					0 => [
						[
							'action' => 'copy',
							'copy' => 'Just a multi-line text.',
							'oldline' => 0,
							'newline' => 0,
						],
					],
					1 => [
						[
							'action' => 'delete',
							'old' => 'Line number two. This line is ' .
								'<del class="diffchange">quite long</del>!',
							'oldline' => 1,
							'count' => 1,
						],
						[
							'action' => 'add',
							'new' => <<<TEXT
Line number two. This line is <ins class="diffchange">now a bit longer</ins>!
\u{00A0}
<ins class="diffchange">And it gets even longer.</ins>
\u{00A0}
TEXT
							,
							'newline' => 1,
							'count' => 2,
						],
					],
					2 => [
						[
							'action' => 'copy',
							'copy' => 'Line number three.',
							'oldline' => 2,
							'newline' => 3,
						]
					],
				],
			],
		];
	}

	/**
	 * @param string $before
	 * @param string $after
	 * @param array[] $expectedOutput
	 * @dataProvider provideFormatWithMarkup
	 */
	public function testMarkupFormat( $before, $after, array $expectedOutput ) {
		$diff = new Diff( $this->splitText( $before ), $this->splitText( $after ) );
		$instance = new LineBasedUnifiedDiffFormatter();
		$output = $instance->format( $diff );
		$this->assertArrayEquals( $expectedOutput, $output );
	}

	public function provideFormatWithMarkup() {
		return [
			[
				'before' => 'Text with [markup] <references />.',
				'after' => 'Text with [markup] <references />.',
				'result' => [
					0 => [
						[
							'action' => 'copy',
							'copy' => 'Text with [markup] &lt;references /&gt;.',
							'oldline' => 0,
							'newline' => 0,
						]
					]
				],
			]
		];
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
