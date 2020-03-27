<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use FauxRequest;
use MediaWikiIntegrationTestCase;
use TwoColConflict\SplitTwoColConflict\ConflictFormValidator;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\ConflictFormValidator
 */
class ConflictFormValidatorTest extends MediaWikiIntegrationTestCase {

	private function getValidator() {
		return TestingAccessWrapper::newFromObject(
			new ConflictFormValidator()
		);
	}

	public function testEmptyGetRequest() {
		/** @var ConflictFormValidator $merger */
		$merger = $this->getValidator();
		$this->assertTrue( $merger->validateRequest( new FauxRequest( [], false ) ) );
	}

	public function provideRequests() {
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
			'Good split-column content' => [
				'params' => [
					'mw-twocolconflict-split-content' => [
						0 => [
							'copy' => 'text1',
						],
						1 => [
							'other' => 'text4',
							'your' => 'text2',
						],
						2 => [
							'copy' => 'text3',
						]
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
						0 => [
							'copy' => 'text1',
						],
						1 => [
							'other' => 'text4',
							'your' => 'text2',
						],
						2 => [
							'other' => 'text3',
						]
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
		$request = new FauxRequest( $requestParams, true );

		$result = $merger->validateRequest( $request );
		$this->assertEquals( $expected, $result );
	}

}
