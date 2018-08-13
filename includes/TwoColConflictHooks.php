<?php

namespace TwoColConflict;

use EditPage;
use MediaWiki\MediaWikiServices;
use OutputPage;
use TwoColConflict\InlineTwoColConflict\InlineTwoColConflictHelper;
use TwoColConflict\SpecialConflictTestPage\TwoColConflictTestEditPage;
use TwoColConflict\SplitTwoColConflict\SplitTwoColConflictHelper;
use User;

/**
 * Hooks for TwoColConflict extension
 *
 * @license GPL-2.0-or-later
 */
class TwoColConflictHooks {

	private static function shouldTwoColConflictBeShown( EditPage $editPage ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		$betaFeatureDisabled = $config->get( 'TwoColConflictBetaFeature' ) &&
			\ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' ) &&
			!\BetaFeatures::isFeatureEnabled( $editPage->getContext()->getUser(), 'twocolconflict' );

		return !$betaFeatureDisabled;
	}

	/**
	 * @param EditPage $editPage
	 */
	public static function onAlternateEdit( EditPage $editPage ) {
		// Skip out on the test page
		if ( get_class( $editPage ) === TwoColConflictTestEditPage::class ) {
			return;
		}

		// Skip out if the feature is disabled
		if ( !self::shouldTwoColConflictBeShown( $editPage ) ) {
			return;
		}

		$config = MediaWikiServices::getInstance()->getMainConfig();
		if ( $config->get( 'TwoColConflictUseInline' ) ) {
			$editPage->setEditConflictHelperFactory( function ( $submitButtonLabel ) use ( $editPage ) {
				return new InlineTwoColConflictHelper(
					$editPage->getTitle(),
					$editPage->getContext()->getOutput(),
					MediaWikiServices::getInstance()->getStatsdDataFactory(),
					$submitButtonLabel
				);
			} );
		} else {
			$editPage->setEditConflictHelperFactory( function ( $submitButtonLabel ) use ( $editPage ) {
				return new SplitTwoColConflictHelper(
					$editPage->getTitle(),
					$editPage->getContext()->getOutput(),
					MediaWikiServices::getInstance()->getStatsdDataFactory(),
					$submitButtonLabel
				);
			} );
		}
	}

	/**
	 * @param EditPage $editPage
	 * @param OutputPage $outputPage
	 */
	public static function onEditPageBeforeConflictDiff(
		EditPage $editPage,
		OutputPage $outputPage
	) {
		if ( \ExtensionRegistry::getInstance()->isLoaded( 'EventLogging' ) ) {
			$user = $outputPage->getUser();
			$baseRevision = $editPage->getBaseRevision();
			$latestRevision = $editPage->getArticle()->getRevision();
			// https://meta.wikimedia.org/w/index.php?title=Schema:TwoColConflictConflict&oldid=18155295
			\EventLogging::logEvent(
				'TwoColConflictConflict',
				18155295,
				[
					'twoColConflictShown' => self::shouldTwoColConflictBeShown( $editPage ),
					'isAnon' => $user->isAnon(),
					'editCount' => (int)$user->getEditCount(),
					'pageNs' => $editPage->getTitle()->getNamespace(),
					'baseRevisionId' => ( $baseRevision ? $baseRevision->getId() : 0 ),
					'latestRevisionId' => ( $latestRevision ? $latestRevision->getId() : 0 ),
					'textUser' => $editPage->textbox2,
				]
			);
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetBetaFeaturePreferences
	 *
	 * @param User $user
	 * @param array[] &$prefs
	 */
	public static function onGetBetaFeaturePreferences( User $user, array &$prefs ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$extensionAssetsPath = $config->get( 'ExtensionAssetsPath' );

		if ( $config->get( 'TwoColConflictBetaFeature' ) ) {
			$prefs['twocolconflict'] = [
				'label-message' => 'twocolconflict-beta-feature-message',
				'desc-message' => 'twocolconflict-beta-feature-description',
				'screenshot' => [
					'ltr' => "$extensionAssetsPath/TwoColConflict/resources/TwoColConflict-beta-features-ltr.svg",
					'rtl' => "$extensionAssetsPath/TwoColConflict/resources/TwoColConflict-beta-features-rtl.svg",
				],
				'info-link'
					=> 'https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Two_Column_Edit_Conflict_View',
				'discussion-link'
					=> 'https://www.mediawiki.org/wiki/Help_talk:Two_Column_Edit_Conflict_View',
				'requirements' => [
					'javascript' => true,
				],
			];
		}
	}

	/**
	 * @param array[] &$testModules
	 * @param \ResourceLoader $rl
	 */
	public static function onResourceLoaderTestModules( array &$testModules, \ResourceLoader $rl ) {
		$testModules['qunit']['ext.TwoColConflict.tests'] = [
			'scripts' => [
				'tests/qunit/InlineTwoColConflict/TwoColConflict.HelpDialog.test.js'
			],
			'dependencies' => [
				'ext.TwoColConflict.Inline.HelpDialog'
			],
			'localBasePath' => dirname( __DIR__ ),
			'remoteExtPath' => 'TwoColConflict',
		];
	}

}
