<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use Language;
use MediaWikiTestCase;
use OOUI\BlankTheme;
use OOUI\Theme;
use TwoColConflict\AnnotatedHtmlDiffFormatter;
use TwoColConflict\SplitTwoColConflict\HtmlSplitConflictView;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\HtmlSplitConflictView
 *
 * Tests to make sure essential elements are there and put in the right order.
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlSplitConflictViewTest extends MediaWikiTestCase {

	public function setUp() : void {
		parent::setUp();
		Theme::setSingleton( new BlankTheme() );
	}

	public function tearDown() : void {
		Theme::setSingleton( null );
		parent::tearDown();
	}

	public function provideIntegrationTests() {
		return [
			'added line' => [ "A\nC", "A\nB\nC", 3, 4 ],
			'changed line' => [ "A\nB\nC", "A\nX\nC", 3, 4 ],
			'copied line' => [ 'A', 'A', 1, 1 ],
			'deleted line' => [ "A\nB\nC", "A\nC", 3, 4 ],
		];
	}

	/**
	 * @dataProvider provideIntegrationTests
	 * @covers \TwoColConflict\SplitTwoColConflict\HtmlSplitConflictView::getHtml
	 */
	public function testIntegration(
		string $storedText,
		string $yourText,
		int $expectedRows,
		int $expectedCells
	) {
		$storedLines = explode( "\n", $storedText );
		$yourLines = explode( "\n", $yourText );

		$formatter = new AnnotatedHtmlDiffFormatter();
		$diff = $formatter->format( $storedLines, $yourLines, $yourLines );

		$view = new HtmlSplitConflictView(
			$this->getTestUser()->getUser(),
			$this->createMock( Language::class )
		);
		$html = $view->getHtml(
			$diff,
			$yourLines,
			$storedLines,
			false
		);

		// All we effectively care about is that no "undefined index" are triggered
		$this->assertIsString( $html );
		$this->assertSame( $expectedRows, substr_count( $html, 'mw-twocolconflict-split-row' ) );
		$this->assertSame( $expectedCells, substr_count( $html, 'mw-twocolconflict-split-column' ) );
	}

	public function provideGetHtml() {
		// inputs inspired by the AnnotatedHtmlDiffFormatterTest
		return [
			[
				[ 'row', 'copy', 'Old 1' ],
				[
					[
						'action' => 'copy',
						'copytext' => 'Just text.',
						'oldline' => 0,
						'count' => 1,
					],
				],
			],
			[
				[ 'row', 'delete', 'Old 1', 'select', 'add', 'New 1' ],
				[
					[
						'action' => 'change',
						'oldhtml' => 'Just text.',
						'oldtext' => 'Just text.',
						'oldline' => 0,
						'oldcount' => 1,
						'newhtml' => 'Just text<ins class="diffchange"> and more</ins>.',
						'newtext' => 'Just text and more.',
						'newline' => 0,
						'newcount' => 1,
					],
				],
			],
			[
				[
					'row', 'copy', 'Old 1',
					'row', 'delete', '', 'select', 'add', 'New 2',
					'row', 'copy', 'Old 2'
				],
				[
					[
						'action' => 'copy',
						'copytext' => 'Just multi-line text.',
						'oldline' => 0,
						'count' => 1,
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => '',
						'newhtml' => '<ins class="diffchange">Line number 1.5.</ins>',
						'newtext' => 'Line number 1.5.',
						'newline' => 1,
						'newcount' => 1,
					],
					[
						'action' => 'copy',
						'copytext' => 'Line number 2.',
						'oldline' => 1,
						'count' => 1,
					],
				]
			],
			[
				[
					'row', 'delete', 'Old 1', 'select', 'add', 'New 1',
					'row', 'copy', 'Old 2',
					'row', 'delete', '', 'select', 'add', 'New 3'
				],
				[
					[
						'action' => 'change',
						'oldhtml' => <<<TEXT
Just multi-line <del class="diffchange">text.</del>
<del class="diffchange">Line number 1.5</del>.
TEXT
						,
						'oldline' => 0,
						'oldcount' => 1,
						'oldtext' => <<<TEXT
Just multi-line text.
Line number 1.5.
TEXT
						,
						'newhtml' => 'Just multi-line <ins class="diffchange">test</ins>.',
						'newtext' => 'Just multi-line test.',
						'newline' => 0,
						'newcount' => 1,
					],
					[
						'action' => 'copy',
						'copytext' => 'Line number 2.',
						'oldline' => 1,
						'count' => 1,
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => '',
						'newhtml' => '<ins class="diffchange">Line number 3.</ins>',
						'newtext' => 'Line number 3.',
						'newline' => 2,
						'newcount' => 1,
					],
				],
			],

		];
	}

	/**
	 * @dataProvider provideGetHtml
	 */
	public function testGetHtmlElementOrder( array $expectedElements, array $diff ) {
		$htmlResult = ( new HtmlSplitConflictView(
			$this->getTestUser()->getUser(),
			$this->createMock( Language::class )
		) )->getHtml(
			$diff,
			[ 'New 1', 'New 2', 'New 3', 'New 4', 'New 5' ],
			[ 'Old 1', 'Old 2', 'Old 3', 'Old 4', 'Old 5' ],
			false
		);

		$this->assertElementsPresentInOrder(
			$htmlResult,
			$expectedElements
		);
	}

	private function assertElementsPresentInOrder( string $html, array $expectedElements ) {
		$offset = 0;
		foreach ( $expectedElements as $element ) {
			switch ( $element ) {
				case 'row':
					$offset = $this->assertDivExistsWithClassValue(
						$html,
						'mw-twocolconflict-split-row',
						$offset
					);
					break;
				case 'select':
					$offset = $this->assertDivExistsWithClassValue(
						$html,
						'mw-twocolconflict-split-selection',
						$offset
					);
					break;
				case 'add':
				case 'delete':
				case 'copy':
					$offset = $this->assertDivExistsWithClassValue(
						$html,
						'mw-twocolconflict-split-' . $element . ' mw-twocolconflict-split-column',
						$offset
					);
					break;
				default:
					$offset = $this->assertEditorExistsWithValue(
						$html,
						$element,
						$offset
					);
			}
		}
	}

	private function assertEditorExistsWithValue( string $html, string $value, int $startPos ) {
		$value .= "\n";

		$pos = strpos( $html, '>' . $value . '</textarea>', $startPos );
		$this->assertTrue(
			$pos !== false,
			'Input element having value "' . $value . '" not found or in wrong position.' . $html
		);
		return $pos;
	}

	private function assertDivExistsWithClassValue( string $html, string $class, int $startPos ) {
		$pos = strpos( $html, '<div class="' . $class . '"', $startPos );
		$this->assertTrue(
			$pos !== false,
			'Div element with class ' . $class . ' not found or in wrong position.'
		);
		return $pos;
	}

}
