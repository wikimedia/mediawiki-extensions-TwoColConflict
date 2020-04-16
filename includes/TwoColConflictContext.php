<?php

namespace TwoColConflict;

use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use Title;
use User;

/**
 * @license GPL-2.0-or-later
 */
class TwoColConflictContext {

	public const BETA_PREFERENCE_NAME = 'twocolconflict';
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

		if ( self::isUsedAsBetaFeature() ) {
			return \BetaFeatures::isFeatureEnabled( $user, self::BETA_PREFERENCE_NAME );
		}

		return $user->getBoolOption( self::OPTOUT_PREFERENCE_NAME );
	}

	/**
	 * @return bool True if TwoColConflict should be provided as a beta feature.
	 *   False if it will be the default conflict workflow.
	 */
	public static function isUsedAsBetaFeature() : bool {
		return MediaWikiServices::getInstance()->getMainConfig()
				->get( 'TwoColConflictBetaFeature' ) &&
			ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' );
	}

}
