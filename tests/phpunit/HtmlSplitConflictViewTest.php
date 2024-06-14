<?php

namespace TwoColConflict\Tests;

use MediaWiki\Language\RawMessage;
use MediaWiki\Output\OutputPage;
use MediaWikiIntegrationTestCase;
use MessageLocalizer;
use TwoColConflict\AnnotatedHtmlDiffFormatter;
use TwoColConflict\Html\HtmlEditableTextComponent;
use TwoColConflict\Html\HtmlSplitConflictView;

/**
 * @covers \TwoColConflict\Html\HtmlSplitConflictView
 * @covers \TwoColConflict\Html\HtmlSideSelectorComponent
 *
 * Tests to make sure essential elements are there and put in the right order.
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlSplitConflictViewTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		// intentionally not reset in teardown, see Icb6901f4d5
		OutputPage::setupOOUI();
	}

	public static function provideIntegrationTests() {
		return [
			'added line' => [ "A\nC", "A\nB\nC", 3, 4 ],
			'changed line' => [ "A\nB\nC", "A\nX\nC", 3, 4 ],
			'copied line' => [ 'A', 'A', 1, 1 ],
			'deleted line' => [ "A\nB\nC", "A\nC", 3, 4 ],
			'bug T266860' => [ "A\n\nB1\n\nC1\n\nD", "A\n\nB2\n\nC2\n\nD", 4, 6 ],
			'bug T268313' => [ "\nA1", "\nA2", 1, 2 ],
		];
	}

	/**
	 * @dataProvider provideIntegrationTests
	 * @covers \TwoColConflict\Html\HtmlSplitConflictView::getHtml
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

	public static function provideGetHtml() {
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
					$this->assertDivExistsWithClassValue(
						$html,
						'mw-twocolconflict-split-row',
						$offset
					);
					break;
				case 'select':
					$this->assertDivExistsWithClassValue(
						$html,
						'mw-twocolconflict-split-selection',
						$offset
					);
					break;
				case 'add':
				case 'delete':
				case 'copy':
					$this->assertDivExistsWithClassValue(
						$html,
						'mw-twocolconflict-split-' . $element . ' mw-twocolconflict-split-column',
						$offset
					);
					break;
				default:
					$this->assertEditorExistsWithValue(
						$html,
						$element,
						$offset
					);
			}
		}
	}

	private function assertEditorExistsWithValue( string $html, string $value, int &$startPos ) {
		$fragment = "<textarea>$value</textarea>";
		$pos = strpos( $html, $fragment, $startPos );
		$this->assertIsInt( $pos, "…$fragment not found after position " . $startPos .
			': …' . substr( $html, $startPos, 1000 ) . '…' );
		$startPos = $pos + strlen( $fragment );
	}

	private function assertDivExistsWithClassValue( string $html, string $class, int &$startPos ) {
		$fragment = '<div class="' . $class;
		$pos = strpos( $html, $fragment, $startPos );
		$this->assertIsInt( $pos, $fragment . '… not found after position ' . $startPos .
			': …' . substr( $html, $startPos, 1000 ) . '…' );
		$startPos = $pos + strlen( $fragment );
	}

	private function createInstance() {
		$editableTextComponent = $this->createMock( HtmlEditableTextComponent::class );
		$editableTextComponent->method( 'getHtml' )
			->willReturnCallback( static function ( $diffHtml, $text ) {
				return "<textarea>$text</textarea>";
			} );

		$localizer = new class implements MessageLocalizer {
			public function msg( $key, ...$params ) {
				return new RawMessage( '' );
			}
		};

		return new HtmlSplitConflictView( $editableTextComponent, $localizer );
	}

}
