<?php

namespace TwoColConflict\ProvideSubmittedText;

use BagOStuff;
use User;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SubmittedTextCache {

	private const CACHE_KEY = 'twoColConflict_yourText';

	/** @var BagOStuff */
	private $cache;

	/**
	 * @param BagOStuff $cache
	 */
	public function __construct( BagOStuff $cache ) {
		$this->cache = $cache;
	}

	/**
	 * @param string $titleDbKey
	 * @param User $user
	 * @param string $session
	 * @param string $text
	 *
	 * @return bool If caching was successful or not.
	 */
	public function stashText( string $titleDbKey, User $user, string $session, string $text ) {
		$key = $this->makeCacheKey( $titleDbKey, $user, $session );
		return $this->cache->set( $key, $text, ExpirationAwareness::TTL_DAY );
	}

	/**
	 * @param string $titleDbKey
	 * @param User $user
	 * @param string $session
	 *
	 * @return string
	 */
	public function fetchText( string $titleDbKey, User $user, string $session ) {
		$key = $this->makeCacheKey( $titleDbKey, $user, $session );
		return $this->cache->get( $key );
	}

	/**
	 * @param string $titleDbKey
	 * @param User $user
	 * @param string $session
	 *
	 * @return string
	 */
	private function makeCacheKey( string $titleDbKey, User $user, string $session ) {
		return $this->cache->makeKey(
			__CLASS__,
			self::CACHE_KEY,
			$titleDbKey,
			$user->getName(),
			$session
		);
	}
}
