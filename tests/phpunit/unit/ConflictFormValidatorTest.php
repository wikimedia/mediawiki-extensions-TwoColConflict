<?php

namespace TwoColConflict\Tests;

use MediaWiki\Request\WebRequest;
use TwoColConflict\ConflictFormValidator;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\ConflictFormValidator
 *
 * @license GPL-2.0-or-later
 */
class ConflictFormValidatorTest extends \MediaWikiUnitTestCase {

	/**
	 * @return ConflictFormValidator
	 */
	private function getValidator() {
		return TestingAccessWrapper::newFromObject(
			new ConflictFormValidator()
		);
	}

	public function testEmptyGetRequest() {
		/** @var ConflictFormValidator $merger */
		$merger = $this->getValidator();
		$this->assertTrue( $merger->validateRequest( $this->createRequest( [] ) ) );
	}

	public static function provideRequests() {
		return [
			'Empty post' => [
				'params' => [],
				'expected' => true,
			],
			'No conflict data' => [
				'params' => [
					'editRevId' => '123',
				],
				'expected' => true,
			],
			'Empty conflict content' => [
				'params' => [
					'mw-twocolconflict-split-content' => [],
				],
				'expected' => false,
			],
			'Good single-column content' => [
				'params' => [
					'mw-twocolconflict-single-column-view' => true,
					'mw-twocolconflict-split-content' => [
						0 => [ 'copy' => 'text1' ],
						1 => [ 'your' => 'text2' ],
						2 => [ 'other' => 'text3' ],
					],
				],
				'expected' => true,
			],
			'Single-column content missing row' => [
				'params' => [
					'mw-twocolconflict-single-column-view' => true,
					'mw-twocolconflict-split-content' => [
						0 => [ 'copy' => 'text1' ],
						1 => [],
						2 => [ 'other' => 'text3' ],
					],
				],
				'expected' => false,
			],
			'Single-column content too many values' => [
				'params' => [
					'mw-twocolconflict-single-column-view' => true,
					'mw-twocolconflict-split-content' => [
						0 => [ 'copy' => 'text1' ],
						1 => [
							'other' => 'text4',
							'your' => 'text2',
						],
						2 => [ 'other' => 'text3' ],
					],
					'mw-twocolconflict-side-selector' => [
						1 => 'your',
					],
				],
				'expected' => false,
			],
			'Single-column content bad type' => [
				'params' => [
					'mw-twocolconflict-single-column-view' => true,
					'mw-twocolconflict-split-content' => [
						0 => [ 'copy' => 'text1' ],
						1 => [ 'other' => [ 'text4' ] ],
						2 => [ 'other' => 'text3' ],
					],
					'mw-twocolconflict-side-selector' => [
						1 => 'your',
					],
				],
				'expected' => false,
			],
			'Good split-column content' => [
				'params' => [
					'mw-twocolconflict-split-content' => [
						0 => [ 'copy' => 'text1' ],
						1 => [
							'other' => 'text4',
							'your' => 'text2',
						],
						2 => [ 'copy' => 'text3' ],
					],
					'mw-twocolconflict-side-selector' => [
						1 => 'your',
					],
				],
				'expected' => true,
			],
			'Split-column missing selections' => [
				'params' => [
					'mw-twocolconflict-split-content' => [
						0 => [ 'copy' => 'text1' ],
						1 => [
							'other' => 'text4',
							'your' => 'text2',
						],
						2 => [ 'other' => 'text3' ],
					],
					'mw-twocolconflict-side-selector' => [],
				],
				'expected' => false,
			],
		];
	}

	/**
	 * @dataProvider provideRequests
	 */
	public function testValidateRequest( array $requestParams, bool $expected ) {
		$merger = $this->getValidator();
		$request = $this->createRequest( $requestParams );

		$result = $merger->validateRequest( $request );
		$this->assertEquals( $expected, $result );
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

}
