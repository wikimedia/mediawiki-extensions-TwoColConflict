<?php

namespace TwoColConflict\Tests;

use Diff;
use MediaWikiTestCase;
use TwoColConflict\AnnotatedHtmlDiffFormatter;

/**
 * @covers \TwoColConflict\AnnotatedHtmlDiffFormatter
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class AnnotatedHtmlDiffFormatterTest extends MediaWikiTestCase {

	/**
	 * @param string $before
	 * @param string $after
	 * @param array[] $expectedOutput
	 * @dataProvider provideFormat
	 */
	public function testFormat( string $before, string $after, array $expectedOutput ) {
		$diff = new Diff( $this->splitText( $before ), $this->splitText( $after ) );
		$instance = new AnnotatedHtmlDiffFormatter();
		$output = $instance->format( $diff );
		$this->assertArrayEquals( $expectedOutput, $output );
	}

	public function provideFormat() {
		return [
			[
				'before' => 'Just text.',
				'after' => 'Just text.',
				'result' => [
					[
						'action' => 'copy',
						'copytext' => 'Just text.',
						'oldline' => 0,
						'count' => 1,
					],
				],
			],
			[
				'before' => 'Just text.',
				'after' => 'Just text. And more.',
				'result' => [
					[
						'action' => 'change',
						'oldhtml' => 'Just text.',
						'newhtml' => 'Just text<ins class="mw-twocolconflict-diffchange">. And more</ins>.',
						'oldline' => 0,
						'newline' => 0,
						'oldcount' => 1,
						'newcount' => 1,
					],
				],
			],
			[
				'before' => 'Just less text.',
				'after' => 'Just less.',
				'result' => [
					[
						'action' => 'change',
						'oldhtml' => 'Just less <del class="mw-twocolconflict-diffchange">text</del>.',
						'newhtml' => 'Just less.',
						'oldline' => 0,
						'newline' => 0,
						'oldcount' => 1,
						'newcount' => 1,
					],
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
					[
						'action' => 'copy',
						'copytext' => 'Just multi-line text.',
						'oldline' => 0,
						'count' => 1,
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">Line number 1.5.</ins>',
						'newline' => 1,
						'newcount' => 1,
					],
					[
						'action' => 'copy',
						'copytext' => 'Line number 2.',
						'oldline' => 1,
						'count' => 1,
					],
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
					[
						'action' => 'copy',
						'copytext' => 'Delete the empty line below.',
						'oldline' => 0,
						'count' => 1,
					],
					[
						'action' => 'delete',
						'oldhtml' => "<del class=\"mw-twocolconflict-diffchange\">\u{00A0}</del>",
						'newhtml' => "\u{00A0}",
						'oldline' => 1,
						'oldcount' => 1,
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
					[
						'action' => 'copy',
						'copytext' => 'Add an empty line below.',
						'oldline' => 0,
						'count' => 1,
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">\u{00A0}</ins>",
						'newline' => 1,
						'newcount' => 1,
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
					[
						'action' => 'copy',
						'copytext' => "Just multi-line text.\nLine number 1.5.",
						'oldline' => 0,
						'count' => 2,
					],
					[
						'action' => 'delete',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">Line number 2.</del>',
						'newhtml' => "\u{00A0}",
						'oldline' => 2,
						'oldcount' => 1,
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
Just multi-line test.
Line number 2.
Line number 3.
TEXT
				,
				'result' => [
					[
						'action' => 'change',
						'oldhtml' => <<<TEXT
Just multi-line <del class="mw-twocolconflict-diffchange">text.</del>
<del class="mw-twocolconflict-diffchange">Line number 1.5</del>.
TEXT
						,
						'newhtml' => 'Just multi-line <ins class="mw-twocolconflict-diffchange">test</ins>.',
						'oldline' => 0,
						'newline' => 0,
						'oldcount' => 2,
						'newcount' => 1,
					],
					[
						'action' => 'copy',
						'copytext' => 'Line number 2.',
						'oldline' => 2,
						'count' => 1,
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">Line number 3.</ins>',
						'newline' => 2,
						'newcount' => 1,
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
					[
						'action' => 'change',
						'oldhtml' => <<<TEXT
Just multi-line <del class="mw-twocolconflict-diffchange">text</del>.
<del class="mw-twocolconflict-diffchange">To change </del>number 2.
<del class="mw-twocolconflict-diffchange">To change </del>number 3.
TEXT
						,
						'newhtml' =>
// @codingStandardsIgnoreStart
<<<TEXT
Just multi-line <ins class="mw-twocolconflict-diffchange">test</ins>.
<ins class="mw-twocolconflict-diffchange">Line </ins>number 2 <ins class="mw-twocolconflict-diffchange">changed</ins>.
<ins class="mw-twocolconflict-diffchange">Line </ins>number 3 <ins class="mw-twocolconflict-diffchange">also changed</ins>.
TEXT
// @codingStandardsIgnoreEnd
						,
						'oldline' => 0,
						'newline' => 0,
						'oldcount' => 3,
						'newcount' => 3,
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
					[
						'action' => 'copy',
						'copytext' => 'Just a multi-line text.',
						'oldline' => 0,
						'count' => 1,
					],
					[
						'action' => 'change',
						'oldhtml' =>
// @codingStandardsIgnoreStart
<<<TEXT
Line number two. <del class="mw-twocolconflict-diffchange">This </del>line <del class="mw-twocolconflict-diffchange">is </del>quite long!
<del class="mw-twocolconflict-diffchange">And that's line number three - even longer than the line before.</del>
\u{00A0}
<del class="mw-twocolconflict-diffchange">Just another line with an empty line above</del>.
TEXT
// @codingStandardsIgnoreEnd
						,
						'newhtml' =>
// @codingStandardsIgnoreStart
<<<TEXT
<ins class="mw-twocolconflict-diffchange">Add something new.</ins>
Line number two. <ins class="mw-twocolconflict-diffchange">Now </ins>line <ins class="mw-twocolconflict-diffchange">number three and </ins>quite long!
<ins class="mw-twocolconflict-diffchange">Add more new stuff</ins>.
TEXT
// @codingStandardsIgnoreEnd
						,
						'oldline' => 1,
						'newline' => 1,
						'oldcount' => 3,
						'newcount' => 3,
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
					[
						'action' => 'copy',
						'copytext' => 'Just a multi-line text.',
						'oldline' => 0,
						'count' => 1,
					],
					[
						'action' => 'change',
						'oldhtml' => 'Line number two. This line is ' .
							'<del class="mw-twocolconflict-diffchange">quite long</del>!',
						'newhtml' => <<<TEXT
Line number two. This line is <ins class="mw-twocolconflict-diffchange">now a bit longer</ins>!
\u{00A0}
<ins class="mw-twocolconflict-diffchange">And it gets even longer.</ins>
\u{00A0}
TEXT
						,
						'oldline' => 1,
						'newline' => 1,
						'oldcount' => 1,
						'newcount' => 2,
					],
					[
						'action' => 'copy',
						'copytext' => 'Line number three.',
						'oldline' => 2,
						'count' => 1,
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
	public function testMarkupFormat( string $before, string $after, array $expectedOutput ) {
		$diff = new Diff( $this->splitText( $before ), $this->splitText( $after ) );
		$instance = new AnnotatedHtmlDiffFormatter();
		$output = $instance->format( $diff );
		$this->assertArrayEquals( $expectedOutput, $output );
	}

	public function provideFormatWithMarkup() {
		return [
			'copy' => [
				'before' => 'Text with [markup] <references />.',
				'after' => 'Text with [markup] <references />.',
				'result' => [
					[
						'action' => 'copy',
						'copytext' => 'Text with [markup] &lt;references /&gt;.',
						'oldline' => 0,
						'count' => 1,
					],
				],
			],
			'delete' => [
				'before' => "Copied <b>text</b>.\nText with [markup] <references />.",
				'after' => 'Copied <b>text</b>.',
				'result' => [
					[
						'action' => 'copy',
						'copytext' => 'Copied &lt;b&gt;text&lt;/b&gt;.',
						'oldline' => 0,
						'count' => 1,
					],
					[
						'action' => 'delete',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">' .
							'Text with [markup] &lt;references /&gt;.</del>',
						'newhtml' => "\u{00A0}",
						'oldline' => 1,
						'oldcount' => 1,
					],
				],
			],
			'add' => [
				'before' => 'Copied <b>text</b>.',
				'after' => "Copied <b>text</b>.\nText with [markup] <references />.",
				'result' => [
					[
						'action' => 'copy',
						'copytext' => 'Copied &lt;b&gt;text&lt;/b&gt;.',
						'oldline' => 0,
						'count' => 1,
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">' .
							'Text with [markup] &lt;references /&gt;.</ins>',
						'newline' => 1,
						'newcount' => 1,
					],
				],
			],
			'change' => [
				'before' => 'Test with [markup] <references />.',
				'after' => 'Text with [markup] <references />.',
				'result' => [
					[
						'action' => 'change',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">' .
							'Test </del>with [markup] &lt;references /&gt;.',
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">' .
							'Text </ins>with [markup] &lt;references /&gt;.',
						'oldline' => 0,
						'newline' => 0,
						'oldcount' => 1,
						'newcount' => 1,
					],
				],
			],
		];
	}

	/**
	 * @param string $text
	 *
	 * @return string[]
	 */
	private function splitText( string $text ) : array {
		return preg_split( '/\n(?!\n)/', $text );
	}

}
