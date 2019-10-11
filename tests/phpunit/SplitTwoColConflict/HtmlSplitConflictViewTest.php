<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use MediaWikiTestCase;
use TwoColConflict\SplitTwoColConflict\HtmlSplitConflictView;
use OOUI\BlankTheme;
use OOUI\Theme;

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

	public function provideGetHtml() {
		// inputs inspired by the LineBasedUnifiedDiffFormatterTest
		return [
			[
				[ 'row', 'copy', 'Old 1' ],
				[
					[
						[
							'action' => 'copy',
							'copy' => 'Just text.',
							'oldline' => 0,
							'count' => 1,
						]
					],
				],
			],
			[
				[ 'row', 'delete', 'Old 1', 'select', 'add', 'New 1' ],
				[
					[
						[
							'action' => 'delete',
							'old' => 'Just text.',
							'oldline' => 0,
							'count' => 1,
						],
						[
							'action' => 'add',
							'new' => 'Just text<ins class="diffchange"> and more</ins>.',
							'newline' => 0,
							'count' => 1,
						],
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
						[
							'action' => 'copy',
							'copy' => 'Just multi-line text.',
							'oldline' => 0,
							'count' => 1,
						]
					],
					[
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
							'count' => 1,
						]
					]
				],
			],
			[
				[
					'row', 'delete', 'Old 1', 'select', 'add', 'New 1',
					'row', 'copy', 'Old 2',
					'row', 'delete', '', 'select', 'add', 'New 3'
				],
				[
					[
						[
							'action' => 'delete',
							'old' =>
								<<<TEXT
Just multi-line <del class="diffchange">text.</del>
<del class="diffchange">Line number 1.5</del>.
TEXT
							,
							'oldline' => 0,
							'count' => 1,
						],
						[
							'action' => 'add',
							'new' => 'Just multi-line <ins class="diffchange">test</ins>.',
							'newline' => 0,
							'count' => 1,
						]
					],
					[
						[
							'action' => 'copy',
							'copy' => 'Line number 2.',
							'oldline' => 1,
							'count' => 1,
						]
					],
					[
						[
							'action' => 'add',
							'new' => '<ins class="diffchange">Line number 3.</ins>',
							'newline' => 2,
							'count' => 1,
						]
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
			new \Language(),
			[]
		) )->getHtml(
			$diff,
			[ 'New 1', 'New 2', 'New 3', 'New 4', 'New 5' ],
			[ 'Old 1', 'Old 2', 'Old 3', 'Old 4', 'Old 5' ]
		);

		$this->assertElementsPresentInOrder(
			$htmlResult,
			$expectedElements
		);
	}

	private function assertElementsPresentInOrder( $html, array $expectedElements ) {
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

	private function assertEditorExistsWithValue( $html, $value, $startPos ) {
		$value .= "\n";

		$pos = strpos( $html, '>' . $value . '</textarea>', $startPos );
		$this->assertTrue(
			$pos !== false,
			'Input element having value "' . $value . '" not found or in wrong position.' . $html
		);
		return $pos;
	}

	private function assertDivExistsWithClassValue( $html, $class, $startPos ) {
		$pos = strpos( $html, '<div class="' . $class . '"', $startPos );
		$this->assertTrue(
			$pos !== false,
			'Div element with class ' . $class . ' not found or in wrong position.'
		);
		return $pos;
	}

}
