<?php

namespace TwoColConflict;

use EditPage;
use MediaWiki\MediaWikiServices;
use OutputPage;
use TwoColConflict\InlineTwoColConflict\InlineTwoColConflictHelper;
use TwoColConflict\SpecialConflictTestPage\TwoColConflictTestEditPage;
use TwoColConflict\SplitTwoColConflict\SplitConflictMerger;
use TwoColConflict\SplitTwoColConflict\SplitTwoColConflictHelper;
use User;
use WebRequest;

/**
 * Hook handlers for the TwoColConflict extension.
 *
 * @license GPL-2.0-or-later
 */
class TwoColConflictHooks {

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	private static function shouldTwoColConflictBeShown( User $user ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		if ( $config->get( 'TwoColConflictBetaFeature' ) &&
			\ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' )
		) {
			return \BetaFeatures::isFeatureEnabled( $user, 'twocolconflict' );
		}

		return true;
	}

	private static function shouldUseSplitInterface( WebRequest $request ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		return !$config->get( 'TwoColConflictUseInline' ) ||
			$request->getCookie( 'mw-twocolconflict-split-ui', '' );
	}

	/**
	 * @param EditPage $editPage
	 */
	public static function onAlternateEdit( EditPage $editPage ) {
		// Skip out on the test page
		if ( $editPage instanceof TwoColConflictTestEditPage ) {
			return;
		}

		// Skip out if the feature is disabled
		if ( !self::shouldTwoColConflictBeShown( $editPage->getContext()->getUser() ) ) {
			return;
		}

		if ( !self::shouldUseSplitInterface( $editPage->getContext()->getRequest() ) ) {
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
					$submitButtonLabel,
					$editPage->summary
				);
			} );
		}
	}

	public static function onImportFormData( EditPage $editPage, \WebRequest $request ) {
		$sideSelection = $request->getArray( 'mw-twocolconflict-side-selector' );

		if ( $request->getBool( 'mw-twocolconflict-submit' ) && $sideSelection !== null ) {
			$editPage->textbox1 = SplitConflictMerger::mergeSplitConflictResults(
				$request->getArray( 'mw-twocolconflict-split-content' ),
				$request->getArray( 'mw-twocolconflict-split-linefeeds' ),
				$sideSelection
			);
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
					'twoColConflictShown' => self::shouldTwoColConflictBeShown( $user ),
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

	public static function onEditPageShowEditFormInitial(
		EditPage $editPage,
		OutputPage $outputPage
	) {
		if ( $editPage instanceof TwoColConflictTestEditPage ||
			$outputPage->getRequest()->getArray( 'mw-twocolconflict-side-selector' ) === null ||
			!self::shouldTwoColConflictBeShown( $editPage->getContext()->getUser() ) ||
			!self::shouldUseSplitInterface( $editPage->getContext()->getRequest() )
		) {
			return;
		}

		if ( $editPage->formtype === 'preview' || $editPage->formtype === 'diff' ) {
			$editPage->isConflict = true;
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
	 * @codeCoverageIgnore
	 *
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
