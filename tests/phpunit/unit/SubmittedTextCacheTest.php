<?php

namespace TwoColConflict\Tests;

use MediaWiki\Session\SessionId;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserIdentityValue;
use TwoColConflict\ProvideSubmittedText\SubmittedTextCache;
use UnexpectedValueException;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\TestingAccessWrapper;

/**
 * @coversDefaultClass \TwoColConflict\ProvideSubmittedText\SubmittedTextCache
 *
 * @license GPL-2.0-or-later
 */
class SubmittedTextCacheTest extends \MediaWikiUnitTestCase {

	/**
	 * @covers ::makeCacheKey
	 * @dataProvider provideMakeCacheKey
	 */
	public function testMakeCacheKey(
		string $prefixedDbKey,
		UserIdentity $user,
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

	public static function provideMakeCacheKey() {
		return [
			'logged-in user, non-main namespace' => [
				'title' => 'Project:TestArticle',
				'user' => UserIdentityValue::newRegistered( 1000, '' ),
				'sessionId' => new SessionId( 'abc123' ),
				'expected' => 'twoColConflict_yourText:Project:TestArticle:1000',
			],
			'logged-in user' => [
				'title' => 'TestArticle',
				'user' => UserIdentityValue::newRegistered( 1000, '' ),
				'sessionId' => new SessionId( 'abc123' ),
				'expected' => 'twoColConflict_yourText:TestArticle:1000',
			],
			'logged-in user, no session' => [
				'title' => 'TestArticle',
				'user' => UserIdentityValue::newRegistered( 1000, '' ),
				'sessionId' => new SessionId( 'abc123' ),
				'expected' => 'twoColConflict_yourText:TestArticle:1000',
			],
			'anonymous user' => [
				'title' => 'TestArticle',
				'user' => UserIdentityValue::newAnonymous( '' ),
				'sessionId' => new SessionId( 'abc123' ),
				'expected' => 'twoColConflict_yourText:TestArticle:0:abc123',
			],
			'anonymous user, no session' => [
				'title' => 'TestArticle',
				'user' => UserIdentityValue::newAnonymous( '' ),
				'sessionId' => null,
				'expected' => null,
			],
		];
	}

}
