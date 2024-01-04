<?php

namespace TwoColConflict\Tests;

use MediaWiki\EditPage\EditPage;
use MediaWiki\Request\WebRequest;
use TwoColConflict\Hooks\EditPageImportFormDataHookHandler;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\Hooks\EditPageImportFormDataHookHandler
 *
 * @license GPL-2.0-or-later
 */
class EditPageImportFormDataHookHandlerTest extends \MediaWikiUnitTestCase {

	public static function provideWebRequests() {
		return [
			'empty request' => [ [], '' ],
			'no content' => [ [ 'mw-twocolconflict-split-content' => [], ], '' ],
			'minimal non-empty request' => [
				[
					'mw-twocolconflict-split-content' => [ [ 'copy' => 'a' ] ],
				],
				'a'
			],
			'bad non-array elements' => [
				[
					'mw-twocolconflict-split-content' => [ 'bad' ],
					'mw-twocolconflict-split-linefeeds' => [ 1 ],
				],
				"bad\n"
			],
			'valid single column request' => [
				[
					'mw-twocolconflict-single-column-view' => true,
					'mw-twocolconflict-split-content' => [ [ 'other' => 'a' ] ],
				],
				'a'
			],
			'single column request with invalid content' => [
				[
					'mw-twocolconflict-single-column-view' => true,
					'mw-twocolconflict-split-content' => [
						[ 'other' => 'a' ],
						[ 'your' => 'b' ],
						[ 'other' => 'c', 'bad key' => 'bad value' ],
						'bad string',
					],
				],
				"b\nbad value\nbad string"
			],
			'single column request with invalid side selection' => [
				[
					'mw-twocolconflict-single-column-view' => true,
					'mw-twocolconflict-split-content' => [
						[ 'other' => 'a' ],
						[ 'your' => 'b' ],
					],
					'mw-twocolconflict-side-selector' => [ 'bad' ],
				],
				'b'
			],

			'trivial copy situation' => [
				[
					'mw-twocolconflict-side-selector' => [],
					'mw-twocolconflict-split-content' => [
						1 => [ 'copy' => 'abc' ],
					],
					'mw-twocolconflict-split-linefeeds' => [
						1 => [ 'copy' => 0 ],
					],
				],
				'abc',
			],
			'trailing linefeeds in the content are ignored' => [
				[
					'mw-twocolconflict-side-selector' => [
						1 => 'other',
					],
					'mw-twocolconflict-split-content' => [
						1 => [ 'other' => "abc\n", 'your' => 'def' ],
					],
					'mw-twocolconflict-split-linefeeds' => [
						1 => [ 'other' => 0 ],
					],
				],
				'abc',
			],
			'original trailing linefeed is restored' => [
				[
					'mw-twocolconflict-side-selector' => [
						1 => 'other',
					],
					'mw-twocolconflict-split-content' => [
						1 => [ 'other' => "abc\n\n", 'your' => 'def' ],
					],
					'mw-twocolconflict-split-linefeeds' => [
						1 => [ 'other' => 1 ],
					],
				],
				"abc\n",
			],
			'all possibilities in one request' => [
				[
					'mw-twocolconflict-side-selector' => [
						2 => 'other',
						4 => 'your',
					],
					'mw-twocolconflict-split-content' => [
						1 => [ 'copy' => 'a' ],
						2 => [ 'other' => 'b other', 'your' => 'b your' ],
						3 => [ 'copy' => 'c' ],
						4 => [ 'other' => 'd other', 'your' => 'd your' ],
					],
					'mw-twocolconflict-split-linefeeds' => [
						4 => [ 'your' => 0 ],
					],
				],
				"a\nb other\nc\nd your",
			],
		];
	}

	/**
	 * @dataProvider provideWebRequests
	 */
	public function testonEditPage__importFormData( array $requestData, string $expectedWikitext ) {
		$editPage = $this->createMock( EditPage::class );
		$request = $this->createRequest( $requestData );
		( new EditPageImportFormDataHookHandler )->onEditPage__importFormData( $editPage, $request );
		$this->assertSame( $expectedWikitext, $editPage->textbox1 );
	}

	/**
	 * @param array $requestParams
	 *
	 * @return WebRequest
	 */
	private function createRequest( array $requestParams ) {
		$request = $this->createMock( WebRequest::class );
		$getter = static function ( string $name, $default ) use ( $requestParams ) {
			return $requestParams[$name] ?? $default;
		};
		$request->method( 'getArray' )->willReturnCallback( $getter );
		$request->method( 'getBool' )->willReturnCallback( $getter );
		return $request;
	}

	public static function provideTalkPageRequests() {
		return [
			'empty' => [
				'contentRows' => [],
				'extraLineFeeds' => [],
				'expected' => [ [], [] ]
			],
			'other first' => [
				'contentRows' => [
					[ 'other' => '' ],
					[ 'your' => '' ],
				],
				'extraLineFeeds' => [ 1, 2 ],
				'expected' => [
					[
						[ 'your' => '' ],
						[ 'other' => '' ],
					],
					[ 2, 1 ]
				]
			],
			'leave copy rows untouched' => [
				'contentRows' => [
					[ 'copy' => 'a' ],
					[ 'copy' => 'b' ],
					[ 'other' => '' ],
					[ 'your' => '' ],
					[ 'copy' => 'c' ],
				],
				'extraLineFeeds' => [ 1, 2, 3, 4, 5 ],
				'expected' => [
					[
						[ 'copy' => 'a' ],
						[ 'copy' => 'b' ],
						[ 'your' => '' ],
						[ 'other' => '' ],
						[ 'copy' => 'c' ],
					],
					[ 1, 2, 4, 3, 5 ]
				]
			],
			'multiple pairs to swap' => [
				'contentRows' => [
					[ 'other' => 'a' ],
					[ 'your' => 'b' ],
					[ 'other' => 'c' ],
					[ 'your' => 'd' ],
				],
				'extraLineFeeds' => [],
				'expected' => [
					[
						[ 'your' => 'b' ],
						[ 'other' => 'a' ],
						[ 'your' => 'd' ],
						[ 'other' => 'c' ],
					],
					[ 0, 0, 0, 0 ]
				]
			],
			'do not swap back if my row is alreday before' => [
				'contentRows' => [
					[ 'your' => '' ],
					[ 'other' => '' ],
				],
				'extraLineFeeds' => [],
				'expected' => [
					[
						[ 'your' => '' ],
						[ 'other' => '' ],
					],
					[]
				]
			],
		];
	}

	/**
	 * @dataProvider provideTalkPageRequests
	 */
	public function testSwapTalkComments(
		array $contentRows,
		array $extraLineFeeds,
		array $expected
	) {
		/** @var EditPageImportFormDataHookHandler $hooks */
		$hooks = TestingAccessWrapper::newFromClass( EditPageImportFormDataHookHandler::class );
		$this->assertSame( $expected, $hooks->swapTalkComments( $contentRows, $extraLineFeeds ) );
	}

}
