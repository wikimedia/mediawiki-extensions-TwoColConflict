<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use Language;
use MediaWikiTestCase;
use Message;
use MessageLocalizer;
use OOUI\BlankTheme;
use OOUI\Theme;
use TwoColConflict\AnnotatedHtmlDiffFormatter;
use TwoColConflict\SplitTwoColConflict\HtmlEditableTextComponent;
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

		$html = $this->createInstance()->getHtml( $diff, false );

		// All we effectively care about is that no "undefined index" are triggered
		$this->assertIsString( $html );
		$this->assertSame( $expectedRows, substr_count( $html, 'mw-twocolconflict-split-row' ) );
		$this->assertSame( $expectedCells, substr_count( $html, 'mw-twocolconflict-split-column' ) );
	}

	public function provideGetHtml() {
		// inputs inspired by the AnnotatedHtmlDiffFormatterTest
		return [
			[
				[ 'row', 'copy', 'Just text.' ],
				[
					[
						'action' => 'copy',
						'copytext' => 'Just text.',
					],
				],
			],
			[
				[ 'row', 'delete', 'Just text.', 'select', 'add', 'Just text and more.' ],
				[
					[
						'action' => 'change',
						'oldhtml' => 'Just text.',
						'oldtext' => 'Just text.',
						'newhtml' => 'Just text<ins class="diffchange"> and more</ins>.',
						'newtext' => 'Just text and more.',
					],
				],
			],
			[
				[
					'row', 'copy', 'Just multi-line text.',
					'row', 'delete', '', 'select', 'add', 'Line number 1.5.',
					'row', 'copy', 'Line number 2.',
				],
				[
					[
						'action' => 'copy',
						'copytext' => 'Just multi-line text.',
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => '',
						'newhtml' => '<ins class="diffchange">Line number 1.5.</ins>',
						'newtext' => 'Line number 1.5.',
					],
					[
						'action' => 'copy',
						'copytext' => 'Line number 2.',
					],
				]
			],
			[
				[
					'row', 'delete', "Just multi-line text.\nLine number 1.5.",
						'select', 'add', 'Just multi-line test.',
					'row', 'copy', 'Line number 2.',
					'row', 'delete', '', 'select', 'add', 'Line number 3.'
				],
				[
					[
						'action' => 'change',
						'oldhtml' => <<<TEXT
Just multi-line <del class="diffchange">text.</del>
<del class="diffchange">Line number 1.5</del>.
TEXT
						,
						'oldtext' => <<<TEXT
Just multi-line text.
Line number 1.5.
TEXT
						,
						'newhtml' => 'Just multi-line <ins class="diffchange">test</ins>.',
						'newtext' => 'Just multi-line test.',
					],
					[
						'action' => 'copy',
						'copytext' => 'Line number 2.',
					],
					[
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => '',
						'newhtml' => '<ins class="diffchange">Line number 3.</ins>',
						'newtext' => 'Line number 3.',
					],
				],
			],

		];
	}

	/**
	 * @dataProvider provideGetHtml
	 */
	public function testGetHtmlElementOrder( array $expectedElements, array $diff ) {
		$html = $this->createInstance()->getHtml( $diff, false );

		$this->assertElementsPresentInOrder(
			$html,
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
			$offset++;
		}
	}

	private function assertEditorExistsWithValue( string $html, string $value, int $startPos ) {
		if ( $value !== '' ) {
			// HtmlEditableTextComponent::getHtml() might enforce one newline at the end
			$value .= "\n";
			if ( $value[0] === "\n" ) {
				// Html::textarea() might add an extra newline at the start
				$value = "\n" . $value;
			}
		}
		$fragment = '>' . $value . '</textarea>';
		$pos = strpos( $html, $fragment, $startPos );
		$this->assertIsInt( $pos, "…$fragment not found after position " . $startPos .
			': …' . substr( $html, $startPos, 1000 ) . '…' );
		return $pos;
	}

	private function assertDivExistsWithClassValue( string $html, string $class, int $startPos ) {
		$fragment = '<div class="' . $class . '"';
		$pos = strpos( $html, $fragment, $startPos );
		$this->assertIsInt( $pos, $fragment . '… not found after position ' . $startPos .
			': …' . substr( $html, $startPos, 1000 ) . '…' );
		return $pos;
	}

	private function createInstance() {
		$localizer = $this->createMock( MessageLocalizer::class );
		$localizer->method( 'msg' )->willReturn( $this->createMock( Message::class ) );

		return new HtmlSplitConflictView( new HtmlEditableTextComponent(
			$localizer,
			$this->getTestUser()->getUser(),
			$this->createMock( Language::class )
		) );
	}

}
