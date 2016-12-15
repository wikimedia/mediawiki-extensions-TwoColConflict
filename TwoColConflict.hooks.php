<?php

use MediaWiki\MediaWikiServices;

/**
 * Hooks for TwoColConflict extension
 *
 * @file
 * @ingroup Extensions
 * @license GPL-2.0+
 */

class TwoColConflictHooks {

	public static function onAlternateEdit( EditPage $editPage ) {
		global $wgHooks;
		$config = MediaWikiServices::getInstance()->getMainConfig();

		/**
		 * If this extension is configured to be a beta feature, and the BetaFeatures extension
		 * is loaded then require the current user to have the feature enabled.
		 */
		if (
			$config->get( 'TwoColConflictBetaFeature' ) &&
			class_exists( BetaFeatures::class ) &&
			!BetaFeatures::isFeatureEnabled( $editPage->getContext()->getUser(), 'twocolconflict' )
		) {
			return true;
		}

		$key = array_search( 'TwoColConflictHooks::onAlternateEdit', $wgHooks );
		unset( $wgHooks[ 'AlternateEdit' ][ $key ] );

		$twoColConflictPage = new TwoColConflictPage( $editPage->mArticle );
		$twoColConflictPage->edit();

		return false;
	}

	public static function getBetaFeaturePreferences( User $user, array &$prefs ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$extensionAssetsPath = $config->get( 'ExtensionAssetsPath' );

		if ( $config->get( 'TwoColConflictBetaFeature' ) ) {
			$prefs['twocolconflict'] = [
				'label-message' => 'twoColConflict-beta-feature-message',
				'desc-message' => 'twoColConflict-beta-feature-description',
				'screenshot' => [
					'ltr' => "$extensionAssetsPath/TwoColConflict/resources/TwoColConflict-beta-features-ltr.svg",
					'rtl' => "$extensionAssetsPath/TwoColConflict/resources/TwoColConflict-beta-features-rtl.svg",
				],
				'info-link' => 'https://www.mediawiki.org/wiki/Extension:TwoColConflict',
				'discussion-link' => 'https://www.mediawiki.org/wiki/Extension_talk:TwoColConflict',
			];
		}
	}

}
