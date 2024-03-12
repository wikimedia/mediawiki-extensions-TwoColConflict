<?php

namespace TwoColConflict\Tests;

use TwoColConflict\AnnotatedHtmlDiffFormatter;

/**
 * @covers \TwoColConflict\AnnotatedHtmlDiffFormatter
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class AnnotatedHtmlDiffFormatterTest extends \MediaWikiUnitTestCase {

	/**
	 * @param string $before
	 * @param string $after
	 * @param array[] $expectedOutput
	 * @dataProvider provideFormat
	 */
	public function testFormat( string $before, string $after, array $expectedOutput ) {
		$instance = new AnnotatedHtmlDiffFormatter();
		$output = $instance->format(
			explode( "\n", $before ),
			explode( "\n", $after ),
			explode( "\n", $this->preSaveTransform( $after ) )
		);
		$this->assertSame( $expectedOutput, $output );
	}

	public static function provideFormat() {
		return [
			[
				'before' => 'Just text.',
				'after' => 'Just text.',
				'result' => [
					[
						'action' => 'copy',
						'copytext' => 'Just text.',
					],
				],
			],
			[
				'before' => 'Just text.',
				'after' => 'Just text. And more.<ref>Example</ref> --~~~~',
				'result' => [
					[
						'action' => 'change',
						'oldhtml' => 'Just text.',
						'oldtext' => 'Just text.',
						'newhtml' => 'Just text. <ins class="mw-twocolconflict-diffchange">' .
							'And more.&lt;ref&gt;Example&lt;/ref&gt; --[[User signature]]</ins>',
						'newtext' => 'Just text. And more.<ref>Example</ref> --~~~~',
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
						'oldtext' => 'Just less text.',
						'newhtml' => 'Just less.',
						'newtext' => 'Just less.',
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
Line number 1.5.<ref>Example</ref> --~~~~
Line number 2.
TEXT
				,
				'result' => [
					[
						'action' => 'copy',
						'copytext' => 'Just multi-line text.',
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => null,
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">Line number 1.5.' .
							'&lt;ref&gt;Example&lt;/ref&gt; --[[User signature]]</ins>',
						'newtext' => 'Line number 1.5.<ref>Example</ref> --~~~~',
					],
					[
						'action' => 'copy',
						'copytext' => 'Line number 2.',
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
					],
					[
						'action' => 'delete',
						'oldhtml' => "<del class=\"mw-twocolconflict-diffchange\">\u{00A0}</del>",
						'oldtext' => '',
						'newhtml' => "\u{00A0}",
						'newtext' => null,
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
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => null,
						'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">\u{00A0}</ins>",
						'newtext' => '',
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
					],
					[
						'action' => 'delete',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">Line number 2.</del>',
						'oldtext' => 'Line number 2.',
						'newhtml' => "\u{00A0}",
						'newtext' => null,
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
						'oldtext' => <<<TEXT
Just multi-line text.
Line number 1.5.
TEXT
						,
						'newhtml' => 'Just multi-line <ins class="mw-twocolconflict-diffchange">test</ins>.',
						'newtext' => 'Just multi-line test.',
					],
					[
						'action' => 'copy',
						'copytext' => 'Line number 2.',
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => null,
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">Line number 3.</ins>',
						'newtext' => 'Line number 3.',
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
						'oldtext' => <<<TEXT
Just multi-line text.
To change number 2.
To change number 3.
TEXT
						,
						'newhtml' =>
// phpcs:disable Generic.Files.LineLength
<<<TEXT
Just multi-line <ins class="mw-twocolconflict-diffchange">test</ins>.
<ins class="mw-twocolconflict-diffchange">Line </ins>number 2 <ins class="mw-twocolconflict-diffchange">changed</ins>.
<ins class="mw-twocolconflict-diffchange">Line </ins>number 3 <ins class="mw-twocolconflict-diffchange">also changed</ins>.
TEXT
,
// phpcs:enable
						'newtext' => <<<TEXT
Just multi-line test.
Line number 2 changed.
Line number 3 also changed.
TEXT
						,
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
					],
					[
						'action' => 'change',
						'oldhtml' =>
// phpcs:disable Generic.Files.LineLength
<<<TEXT
Line number two. <del class="mw-twocolconflict-diffchange">This </del>line <del class="mw-twocolconflict-diffchange">is </del>quite long!
<del class="mw-twocolconflict-diffchange">And that's line number three - even longer than the line before.</del>
\u{00A0}
<del class="mw-twocolconflict-diffchange">Just another line with an empty line above</del>.
TEXT
,
// phpcs:enable

						'oldtext' => <<<TEXT
Line number two. This line is quite long!
And that's line number three - even longer than the line before.

Just another line with an empty line above.
TEXT
						,
						'newhtml' =>
// phpcs:disable Generic.Files.LineLength
<<<TEXT
<ins class="mw-twocolconflict-diffchange">Add something new.</ins>
Line number two. <ins class="mw-twocolconflict-diffchange">Now </ins>line <ins class="mw-twocolconflict-diffchange">number three and </ins>quite long!
<ins class="mw-twocolconflict-diffchange">Add more new stuff</ins>.
TEXT
,
// phpcs:enable

						'newtext' => <<<TEXT
Add something new.
Line number two. Now line number three and quite long!
Add more new stuff.
TEXT
						,
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
					],
					[
						'action' => 'change',
						'oldhtml' => 'Line number two. This line is ' .
							'<del class="mw-twocolconflict-diffchange">quite long</del>!',
						'oldtext' => 'Line number two. This line is quite long!',
						'newhtml' => <<<TEXT
Line number two. This line is <ins class="mw-twocolconflict-diffchange">now a bit longer</ins>!
\u{00A0}
<ins class="mw-twocolconflict-diffchange">And it gets even longer.</ins>
\u{00A0}
TEXT
						,
						'newtext' => <<<TEXT
Line number two. This line is now a bit longer!

And it gets even longer.

TEXT
						,
					],
					[
						'action' => 'copy',
						'copytext' => 'Line number three.',
					],
				],
			],
			'bug T248668' => [
				'before' => "Initial comment.\n\nLater comment.",
				'after' => "Initial comment.\n:Inline response.\n\nLater comment.",
				'result' => [
					[
						'action' => 'copy',
						'copytext' => 'Initial comment.',
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => null,
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">:Inline response.</ins>',
						'newtext' => ':Inline response.',
					],
					[
						'action' => 'copy',
						'copytext' => "\nLater comment.",
					],
				],
			],
			'bug T266860' => [
				'before' => "A\n\nB1\n\nC1\n\nD",
				'after' => "A\n\nB2\n\nC2\n\nD",
				'result' => [
					[
						'action' => 'copy',
						'copytext' => "A\n",
					],
					[
						'action' => 'change',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">B1</del>',
						'oldtext' => "B1\n",
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">B2</ins>',
						'newtext' => "B2\n",
					],
					[
						'action' => 'change',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">C1</del>',
						'oldtext' => 'C1',
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">C2</ins>',
						'newtext' => 'C2',
					],
					[
						'action' => 'copy',
						'copytext' => "\nD",
					],
				],
			],
			'bug T268314 and T268313' => [
				'before' => "\n\n\nA1\n\n\nB\n\n\nC1\n\n\n",
				'after' => "\n\n\nA2\n\n\nB\n\n\nC2\n\n\n",
				'result' => [
					[
						'action' => 'change',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">A1</del>',
						'oldtext' => "\n\n\nA1",
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">A2</ins>',
						'newtext' => "\n\n\nA2",
					],
					[
						'action' => 'copy',
						'copytext' => "\n\nB\n\n",
					],
					[
						'action' => 'change',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">C1</del>',
						'oldtext' => "C1\n\n\n",
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">C2</ins>',
						'newtext' => "C2\n\n\n",
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
		$instance = new AnnotatedHtmlDiffFormatter();
		$output = $instance->format(
			explode( "\n", $before ),
			explode( "\n", $after ),
			explode( "\n", $this->preSaveTransform( $after ) )
		);
		$this->assertSame( $expectedOutput, $output );
	}

	public static function provideFormatWithMarkup() {
		return [
			'copy' => [
				'before' => 'Text with [markup] <references />.',
				'after' => 'Text with [markup] <references />.',
				'result' => [
					[
						'action' => 'copy',
						'copytext' => 'Text with [markup] <references />.',
					],
				],
			],
			'delete' => [
				'before' => "Copied <b>text</b>.\nText with [markup] <references />.",
				'after' => 'Copied <b>text</b>.',
				'result' => [
					[
						'action' => 'copy',
						'copytext' => 'Copied <b>text</b>.',
					],
					[
						'action' => 'delete',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">' .
							'Text with [markup] &lt;references /&gt;.</del>',
						'oldtext' => 'Text with [markup] <references />.',
						'newhtml' => "\u{00A0}",
						'newtext' => null,
					],
				],
			],
			'add' => [
				'before' => 'Copied <b>text</b>.',
				'after' => "Copied <b>text</b>.\nText with [markup] <references />.",
				'result' => [
					[
						'action' => 'copy',
						'copytext' => 'Copied <b>text</b>.',
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => null,
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">' .
							'Text with [markup] &lt;references /&gt;.</ins>',
						'newtext' => 'Text with [markup] <references />.',
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
						'oldtext' => 'Test with [markup] <references />.',
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">' .
							'Text </ins>with [markup] &lt;references /&gt;.',
						'newtext' => 'Text with [markup] <references />.',
					],
				],
			],
		];
	}

	public function testT306221() {
		$formatter = new AnnotatedHtmlDiffFormatter();
		$before = explode( "\n", "Before\n\nBefore" );
		$after = explode( "\n", "After\n\nAfter" );
		$this->assertSame( [ 0, 1 ], array_keys( $formatter->format( $before, $after, $after ) ) );
	}

	/**
	 * @see \Parser::pstPass2
	 *
	 * @param string $wikitext
	 *
	 * @return string
	 */
	private function preSaveTransform( string $wikitext ): string {
		return preg_replace( '/~~~+/', '[[User signature]]', $wikitext );
	}

}
