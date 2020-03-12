<?php

namespace TwoColConflict;

use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use User;

/**
 * TwoColConflict Module
 *
 * @license GPL-2.0-or-later
 */
class TwoColConflictContext {

	public const OPTOUT_PREFERENCE_NAME = 'twocolconflict-enabled';

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	public static function shouldTwoColConflictBeShown( User $user ) : bool {
		if ( self::isUsedAsBetaFeature() &&
			ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' )
		) {
			return \BetaFeatures::isFeatureEnabled( $user, 'twocolconflict' );
		}

		return $user->getBoolOption( self::OPTOUT_PREFERENCE_NAME );
	}

	/**
	 * @return bool
	 */
	public static function isUsedAsBetaFeature() : bool {
		return MediaWikiServices::getInstance()->getMainConfig()
			->get( 'TwoColConflictBetaFeature' );
	}
}
