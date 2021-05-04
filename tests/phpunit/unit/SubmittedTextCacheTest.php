<?php

namespace TwoColConflict\Tests;

use BagOStuff;
use MediaWiki\Session\SessionId;
use TwoColConflict\ProvideSubmittedText\SubmittedTextCache;
use UnexpectedValueException;
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
		?SessionId $sessionId,
		?string $expected
	) {
		$backend = $this->createMock( BagOStuff::class );
		$backend->method( 'makeKey' )
			->willReturnCallback(
				static function ( ...$components ) {
					return implode( ':', $components );
				}
			);
		/** @var SubmittedTextCache $cache */
		$cache = TestingAccessWrapper::newFromObject( new SubmittedTextCache( $backend ) );

		if ( !$expected ) {
			$this->expectException( UnexpectedValueException::class );
		}

		$this->assertSame(
			$expected,
			$cache->makeCacheKey( $prefixedDbKey, $user, $sessionId )
		);
	}

	public function provideMakeCacheKey() {
		return [
			'logged-in user, non-main namespace' => [
				'title' => 'Project:TestArticle',
				'user' => $this->newMockUser( 1000 ),
				'sessionId' => new SessionId( 'abc123' ),
				'expected' => 'twoColConflict_yourText:Project:TestArticle:1000',
			],
			'logged-in user' => [
				'title' => 'TestArticle',
				'user' => $this->newMockUser( 1000 ),
				'sessionId' => new SessionId( 'abc123' ),
				'expected' => 'twoColConflict_yourText:TestArticle:1000',
			],
			'logged-in user, no session' => [
				'title' => 'TestArticle',
				'user' => $this->newMockUser( 1000 ),
				'sessionId' => new SessionId( 'abc123' ),
				'expected' => 'twoColConflict_yourText:TestArticle:1000',
			],
			'anonymous user' => [
				'title' => 'TestArticle',
				'user' => $this->newMockUser( 0 ),
				'sessionId' => new SessionId( 'abc123' ),
				'expected' => 'twoColConflict_yourText:TestArticle:0:abc123',
			],
			'anonymous user, no session' => [
				'title' => 'TestArticle',
				'user' => $this->newMockUser( 0 ),
				'sessionId' => null,
				'expected' => null,
			],
		];
	}

	private function newMockUser( int $userId ) {
		$user = $this->createMock( User::class );
		$user->method( 'getId' )
			->willReturn( $userId );
		$user->method( 'isAnon' )
			->willReturn( !$userId );
		return $user;
	}

}
