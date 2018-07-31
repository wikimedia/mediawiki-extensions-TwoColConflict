<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use MediaWikiTestCase;
use TwoColConflict\SplitTwoColConflict\HtmlSplitConflictView;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\HtmlSplitConflictView
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlSplitConflictViewTest extends MediaWikiTestCase {

	public function provideGetHtml() {
		// inputs inspired by the LineBasedUnifiedDiffFormatterTest
		return [
			[
				[ 'row', 'copy' ],
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
				[ 'row', 'delete', 'add' ],
				[
					[
						[
							'action' => 'delete',
							'old' => 'Just text.',
						],
						[
							'action' => 'add',
							'new' => 'Just text<ins class="diffchange"> and more</ins>.',
						],
					],
				],
			],
			[
				[ 'row', 'copy', 'row', 'delete', 'add', 'row', 'copy' ],
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
						],
						[
							'action' => 'copy',
							'copy' => 'Line number 2.',
						]
					]
				],
			],
			[
				[ 'row', 'delete', 'add', 'row', 'copy', 'row', 'delete', 'add' ],
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
						],
						[
							'action' => 'add',
							'new' => 'Just multi-line <ins class="diffchange">test</ins>.',
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

						]
					],
				],
			],

		];
	}

	/**
	 * @dataProvider provideGetHtml
	 */
	public function testGetHtmlElementOrder( $expectedElements, $diff ) {
		$htmlConflictView = new HtmlSplitConflictView();
		$this->assertDivElementsPresentInOrder(
			$htmlConflictView->getHtml( $diff ),
			$expectedElements
		);
	}

	private function assertDivElementsPresentInOrder( $html, $expectedElements ) {
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
				default:
					$offset = $this->assertDivExistsWithClassValue(
						$html,
						'mw-twocolconflict-split-' . $element . ' mw-twocolconflict-split-column',
						$offset
					);
					break;
			}
		}
	}

	private function assertDivExistsWithClassValue( $html, $classValue, $startpos ) {
		$pos = strpos( $html, '<div class="' . $classValue . '"', $startpos );
		$this->assertTrue( $pos !== false );
		return $pos;
	}

}
