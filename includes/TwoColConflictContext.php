<?php

namespace TwoColConflict;

use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use Title;
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
	 * @param Title $title
	 *
	 * @return bool
	 */
	public static function shouldTwoColConflictBeShown( User $user, Title $title ) : bool {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		if ( !$config->get( 'TwoColConflictSuggestResolution' ) &&
			( $title->isTalkPage() || $title->inNamespace( NS_PROJECT ) )
		) {
			return false;
		}

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
