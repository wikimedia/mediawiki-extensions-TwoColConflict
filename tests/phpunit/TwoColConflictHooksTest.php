<?php

namespace TwoColConflict\Tests;

use EditPage;
use TwoColConflict\TwoColConflictHooks;

/**
 * @covers \TwoColConflict\TwoColConflictHooks
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictHooksTest extends \MediaWikiTestCase {

	protected function setUp() {
		parent::setUp();

		$this->setMwGlobals( 'wgFileImporterAccountForSuppressedUsername', '<SUPPRESSED>' );
	}

	public function provideOnAttemptSave() {
		return [
			[
				null,
				null,
				'',
			],
			[
				[],
				[
					1 => [ 'copy' => 'abc' ],
				],
				"abc",
			],
			[
				[
					1 => 'other',
				],
				[
					1 => [ 'other' => "abc\n", 'your' => 'def' ],
				],
				"abc",
			],
			[
				[
					1 => 'other',
				],
				[
					1 => [ 'other' => "abc\n\n", 'your' => 'def' ],
				],
				"abc\n",
			],
			[
				[
					2 => 'other',
					4 => 'your',
				],
				[
					1 => [ 'copy' => 'a' ],
					2 => [ 'other' => 'b other', 'your' => 'b your' ],
					3 => [ 'copy' => 'c' ],
					4 => [ 'other' => 'd other', 'your' => 'd your' ],
				],
				"a\nb other\nc\nd your",
			],
		];
	}

	/**
	 * @dataProvider provideOnAttemptSave
	 */
	public function testOnAttemptSave(
		array $sideSelection = null,
		array $splitContent = null,
		$expected
	) {
		$editPage = $this->createEditPage( $sideSelection, $splitContent );

		TwoColConflictHooks::onAttemptSave( $editPage );
		$this->assertSame( $expected, $editPage->textbox1 );
	}

	public function testOnAttemptSaveNotTriggered() {
		$editPage = $this->createEditPage( [], [], false );

		TwoColConflictHooks::onAttemptSave( $editPage );
		$this->assertSame( '', $editPage->textbox1 );
	}

	/**
	 * @return EditPage
	 */
	private function createEditPage(
		array $sideSelection = null,
		array $splitContent = null,
		$submit = true
	) {
		$context = $this->createMock( \RequestContext::class );
		$context->method( 'getRequest' )
			->willReturn(
				new \FauxRequest( [
					'mw-twocolconflict-submit' => $submit,
					'mw-twocolconflict-side-selector' => $sideSelection,
					'mw-twocolconflict-split-content' => $splitContent,
				] )
			);

		$mock = $this->getMock( EditPage::class, [ 'getContext' ], [], '', false );
		$mock->method( 'getContext' )
			->willReturn( $context );

		return $mock;
	}

}
