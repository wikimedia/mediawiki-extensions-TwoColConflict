<?php

namespace TwoColConflict\Tests;

use EditPage;
use TwoColConflict\TwoColConflictHooks;

/**
 * @covers \TwoColConflict\TwoColConflictHooks
 * @covers \TwoColConflict\SplitTwoColConflict\SplitConflictMerger
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictHooksTest extends \MediaWikiTestCase {

	protected function setUp() : void {
		parent::setUp();

		$this->setMwGlobals( 'wgFileImporterAccountForSuppressedUsername', '<SUPPRESSED>' );
	}

	public function provideOnImportFormData() {
		return [
			[
				null,
				null,
				null,
				'',
			],
			[
				[],
				[
					1 => [ 'copy' => 'abc' ],
				],
				[
					1 => [ 'copy' => 0 ],
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
				[
					1 => [ 'other' => 0 ],
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
				[
					1 => [ 'other' => 1 ],
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
				[
					4 => [ 'your' => 0 ],
				],
				"a\nb other\nc\nd your",
			],
		];
	}

	/**
	 * @dataProvider provideOnImportFormData
	 */
	public function testOnImportFormData(
		?array $sideSelection,
		?array $splitContent,
		?array $splitLineFeeds,
		$expected
	) {
		$editPage = $this->createEditPage();
		$request = $this->createWebRequest( $sideSelection, $splitContent, $splitLineFeeds );

		TwoColConflictHooks::onImportFormData( $editPage, $request );
		$this->assertSame( $expected, $editPage->textbox1 );
	}

	public function testOnImportFormDataNotTriggered() {
		$editPage = $this->createEditPage();
		$request = $this->createWebRequest( null, null, null, false );

		TwoColConflictHooks::onImportFormData( $editPage, $request );
		$this->assertSame( '', $editPage->textbox1 );
	}

	/**
	 * @return EditPage
	 */
	private function createEditPage() {
		return $this->createMock( EditPage::class );
	}

	/**
	 * @return \WebRequest
	 */
	private function createWebRequest(
		array $sideSelection = null,
		array $splitContent = null,
		array $splitLineFeeds = null,
		$submit = true
	) {
		return new \FauxRequest( [
			'mw-twocolconflict-submit' => $submit,
			'mw-twocolconflict-side-selector' => $sideSelection,
			'mw-twocolconflict-split-content' => $splitContent,
			'mw-twocolconflict-split-linefeeds' => $splitLineFeeds,
		] );
	}

}
