<?php

namespace TwoColConflict\Tests;

use BagOStuff;
use TwoColConflict\ProvideSubmittedText\SubmittedTextCache;
use User;
use Wikimedia\TestingAccessWrapper;

/**
 * @coversDefaultClass \TwoColConflict\ProvideSubmittedText\SubmittedTextCache
 */
class SubmittedTextCacheTest extends \MediaWikiUnitTestCase {
	/**
	 * @covers ::makeCacheKey
	 * @dataProvider provideMakeCacheKey
	 */
	public function testMakeCacheKey(
		string $prefixedDbKey,
		User $user,
		string $session,
		string $expected
	) {
		$backend = $this->createMock( BagOStuff::class );
		$backend->method( 'makeKey' )
			->willReturnCallback(
				function ( ...$components ) {
					return implode( ':', $components );
				}
			);
		/** @var SubmittedTextCache $cache */
		$cache = TestingAccessWrapper::newFromObject( new SubmittedTextCache( $backend ) );

		$this->assertSame(
			$expected,
			$cache->makeCacheKey( $prefixedDbKey, $user, $session )
		);
	}

	public function provideMakeCacheKey() {
		return [
			'logged-in user, non-main namespace' => [
				'title' => 'Project:TestArticle',
				'user' => $this->newMockUser( 'Foo' ),
				'session' => 'abc123',
				'expected' => 'TwoColConflict\ProvideSubmittedText\SubmittedTextCache:' .
					'twoColConflict_yourText:Project:TestArticle:Foo:abc123',
			],
			'logged-in user' => [
				'title' => 'TestArticle',
				'user' => $this->newMockUser( 'Foo' ),
				'session' => 'abc123',
				'expected' => 'TwoColConflict\ProvideSubmittedText\SubmittedTextCache:' .
					'twoColConflict_yourText:TestArticle:Foo:abc123',
			],
			'anonymous user' => [
				'title' => 'TestArticle',
				'user' => $this->newMockUser( '1.2.3.4' ),
				'session' => 'abc123',
				'expected' => 'TwoColConflict\ProvideSubmittedText\SubmittedTextCache:' .
					'twoColConflict_yourText:TestArticle:1.2.3.4:abc123',
			],
		];
	}

	private function newMockUser( string $userName ) {
		$user = $this->createMock( User::class );
		$user->method( 'getName' )
			->willReturn( $userName );
		return $user;
	}
}
