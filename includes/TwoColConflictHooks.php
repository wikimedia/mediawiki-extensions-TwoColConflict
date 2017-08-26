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

	/**
	 * @param Article $article
	 * @param User $user
	 * @return bool
	 */
	public static function onCustomEditor( Article $article, User $user ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		/**
		 * If this extension is configured to be a beta feature, and the BetaFeatures extension
		 * is loaded then require the current user to have the feature enabled.
		 */
		if (
			$config->get( 'TwoColConflictBetaFeature' ) &&
			class_exists( BetaFeatures::class ) &&
			!BetaFeatures::isFeatureEnabled( $user, 'twocolconflict' )
		) {
			return true;
		}

		$twoColConflictPage = new TwoColConflictPage( $article );
		$twoColConflictPage->edit();

		return false;
	}

	/**
	 * @param EditPage $editPage
	 * @param Status $status
	 */
	public static function onAttemptSaveAfter( EditPage $editPage, Status $status ) {
		global $wgRequest;

		if ( !$wgRequest->getBool( 'mw-twocolconflict-submit' ) ) {
			return;
		}

		if ( $status->value == EditPage::AS_SUCCESS_UPDATE ) {
			$stats = MediaWikiServices::getInstance()->getStatsdDataFactory();
			$stats->increment( 'TwoColConflict.conflict.resolved' );
		}
	}

	/**
	 * @param User $user
	 * @param array[] &$prefs
	 */
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
				'info-link'
					=> 'https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Two_Column_Edit_Conflict_View',
				'discussion-link'
					=> 'https://www.mediawiki.org/wiki/Help_talk:Two_Column_Edit_Conflict_View',
			];
		}
	}

	/**
	 * @param array &$testModules
	 * @param ResourceLoader $rl
	 * @return bool
	 */
	public static function onResourceLoaderTestModules( array &$testModules, ResourceLoader $rl ) {
		$testModules['qunit']['ext.TwoColConflict.tests'] = [
			'scripts' => [
				'tests/qunit/TwoColConflict.HelpDialog.test.js'
			],
			'dependencies' => [
				'ext.TwoColConflict.HelpDialog'
			],
			'localBasePath' => dirname( __DIR__ ),
			'remoteExtPath' => 'TwoColConflict',
		];

		return true;
	}
}
