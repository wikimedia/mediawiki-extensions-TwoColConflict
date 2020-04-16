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
	public const ENABLED_PREFERENCE = 'twocolconflict-enabled';

	/**
	 * @param User $user
	 * @param Title $title
	 *
	 * @return bool True if the new conflict interface should be used for this
	 *   user and title.  The user may have opted out, or the title namespace
	 *   may be blacklisted for this interface.
	 */
	public static function shouldTwoColConflictBeShown( User $user, Title $title ) : bool {
		if ( self::isEligibleTalkPage( $title ) &&
			!self::isTalkPageSuggesterEnabled()
		) {
			// Temporary feature logic to completely disable on talk pages.
			return false;
		}

		return self::hasUserEnabledFeature( $user );
	}

	/**
	 * @param Title $title
	 * @return bool True if this article is appropriate for the talk page
	 *   workflow, and the interface has been enabled by configuration.
	 */
	public static function shouldTalkPageSuggestionBeConsidered( Title $title ) : bool {
		return self::isTalkPageSuggesterEnabled() &&
			self::isEligibleTalkPage( $title );
	}

	private static function hasUserEnabledFeature( User $user ) : bool {
		if ( self::isUsedAsBetaFeature() ) {
			return \BetaFeatures::isFeatureEnabled( $user, self::BETA_PREFERENCE_NAME );
		}

		return $user->getBoolOption( self::ENABLED_PREFERENCE );
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

	private static function isEligibleTalkPage( Title $title ) : bool {
		return $title->isTalkPage() || $title->inNamespace( NS_PROJECT );
	}

	private static function isTalkPageSuggesterEnabled() : bool {
		return MediaWikiServices::getInstance()->getMainConfig()
			->get( 'TwoColConflictSuggestResolution' );
	}

}
