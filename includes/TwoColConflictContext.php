<?php

namespace TwoColConflict;

use BetaFeatures;
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
	public const HIDE_CORE_HINT_PREFERENCE = 'userjs-twocolconflict-hide-core-hint';

	/**
	 * @param User $user
	 *
	 * @return bool True if the feature is not used as a beta feature, the
	 * user has disabled the feature but has not dismissed the core hint
	 * already.
	 */
	public function shouldCoreHintBeShown( User $user ) {
		return !$user->isAnon() &&
			!$this->isUsedAsBetaFeature() &&
			!$user->getBoolOption( self::ENABLED_PREFERENCE ) &&
			!$user->getBoolOption( self::HIDE_CORE_HINT_PREFERENCE );
	}

	/**
	 * @param User $user
	 * @param Title $title
	 *
	 * @return bool True if the new conflict interface should be used for this
	 *   user and title.  The user may have opted out, or the title namespace
	 *   may be blacklisted for this interface.
	 */
	public function shouldTwoColConflictBeShown( User $user, Title $title ) : bool {
		// T249817: Temporarily disabled on mobile
		if ( ExtensionRegistry::getInstance()->isLoaded( 'MobileFrontend' ) &&
			MediaWikiServices::getInstance()->getService( 'MobileFrontend.Context' )
				->shouldDisplayMobileView()
		) {
			return false;
		}

		if ( $this->isEligibleTalkPage( $title ) &&
			!$this->isTalkPageSuggesterEnabled()
		) {
			// Temporary feature logic to completely disable on talk pages.
			return false;
		}

		return $this->hasUserEnabledFeature( $user );
	}

	/**
	 * @param Title $title
	 * @return bool True if this article is appropriate for the talk page
	 *   workflow, and the interface has been enabled by configuration.
	 */
	public function shouldTalkPageSuggestionBeConsidered( Title $title ) : bool {
		return $this->isTalkPageSuggesterEnabled() &&
			$this->isEligibleTalkPage( $title );
	}

	private function hasUserEnabledFeature( User $user ) : bool {
		if ( $this->isUsedAsBetaFeature() ) {
			return BetaFeatures::isFeatureEnabled( $user, self::BETA_PREFERENCE_NAME );
		}

		return $user->getBoolOption( self::ENABLED_PREFERENCE );
	}

	/**
	 * @return bool True if TwoColConflict should be provided as a beta feature.
	 *   False if it will be the default conflict workflow.
	 */
	public function isUsedAsBetaFeature() : bool {
		return MediaWikiServices::getInstance()->getMainConfig()
				->get( 'TwoColConflictBetaFeature' ) &&
			ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' );
	}

	private function isEligibleTalkPage( Title $title ) : bool {
		return $title->isTalkPage() || $title->inNamespace( NS_PROJECT );
	}

	private function isTalkPageSuggesterEnabled() : bool {
		return MediaWikiServices::getInstance()->getMainConfig()
			->get( 'TwoColConflictSuggestResolution' );
	}

}
