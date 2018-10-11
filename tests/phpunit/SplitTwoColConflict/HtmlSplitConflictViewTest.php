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

	public function setUp() {
		parent::setUp();
		Theme::setSingleton( new BlankTheme() );
	}

	public function tearDown() {
		Theme::setSingleton( null );
		parent::tearDown();
	}

	public function provideGetHtml() {
		// inputs inspired by the LineBasedUnifiedDiffFormatterTest
		return [
			[
				[ 'row', 'copy', 'Just text.' ],
				[
					[
						[
							'action' => 'copy',
							'copy' => 'Just text.',
						]
					],
				],
			],
			[
				[ 'row', 'delete', '1', 'select', 'add', 'a' ],
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
					'row', 'copy', 'Just multi-line text.',
					'row', 'delete', '', 'select', 'add', 'b',
					'row', 'copy', 'Line number 2.'
				],
				[
					[
						[
							'action' => 'copy',
							'copy' => 'Just multi-line text.',
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
						]
					]
				],
			],
			[
				[
					'row', 'delete', '1', 'select', 'add', 'a',
					'row', 'copy', 'Line number 2.',
					'row', 'delete', '', 'select', 'add', 'c'
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
			str_split( 'abcde' ),
			str_split( '12345' )
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
