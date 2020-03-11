<?php

namespace TwoColConflict;

use EditPage;
use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use OutputPage;
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

	private const OPTOUT_PREFERENCE_NAME = 'twocolconflict-enabled';

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	private static function shouldTwoColConflictBeShown( User $user ) : bool {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		if ( $config->get( 'TwoColConflictBetaFeature' ) &&
			ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' )
		) {
			return \BetaFeatures::isFeatureEnabled( $user, 'twocolconflict' );
		}

		return $user->getBoolOption( self::OPTOUT_PREFERENCE_NAME );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/AlternateEdit
	 *
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

		$editPage->setEditConflictHelperFactory( function ( $submitButtonLabel ) use ( $editPage ) {
			return new SplitTwoColConflictHelper(
				$editPage->getTitle(),
				$editPage->getContext()->getOutput(),
				MediaWikiServices::getInstance()->getStatsdDataFactory(),
				$submitButtonLabel,
				$editPage->summary,
				MediaWikiServices::getInstance()->getContentHandlerFactory()
			);
		} );

		$request = $editPage->getContext()->getRequest();
		$contentRows = $request->getArray( 'mw-twocolconflict-split-content' );
		if ( $contentRows ) {
			$sideSelection = $request->getArray( 'mw-twocolconflict-side-selector', [] );
			if ( !SplitConflictMerger::validateSideSelection( $contentRows, $sideSelection ) ) {
				// Mark the conflict as *not* being resolved to trigger it again. This works because
				// EditPage uses editRevId to decide if it's even possible to run into a conflict.
				// If editRevId reflects the most recent revision, it can't be a conflict (again),
				// and the user's input is stored, even if it reverts everything.
				// Warning, this is particularly fragile! This assumes EditPage was not reading the
				// WebRequest values before!
				$request->setVal( 'editRevId', $request->getInt( 'parentRevId' ) );
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPage::importFormData
	 *
	 * @param EditPage $editPage
	 * @param WebRequest $request
	 */
	public static function onImportFormData( EditPage $editPage, WebRequest $request ) {
		$contentRows = $request->getArray( 'mw-twocolconflict-split-content' );
		if ( $contentRows ) {
			$extraLineFeeds = $request->getArray( 'mw-twocolconflict-split-linefeeds', [] );
			$sideSelection = $request->getArray( 'mw-twocolconflict-side-selector', [] );
			$editPage->textbox1 = SplitConflictMerger::mergeSplitConflictResults(
				$contentRows,
				$extraLineFeeds,
				$sideSelection
			);
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPage::showEditForm:initial
	 *
	 * @param EditPage $editPage
	 * @param OutputPage $outputPage
	 */
	public static function onEditPageshowEditFormInitial(
		EditPage $editPage,
		OutputPage $outputPage
	) {
		if ( ExtensionRegistry::getInstance()->isLoaded( 'EventLogging' ) ) {
			$outputPage->addModules( 'ext.TwoColConflict.JSCheck' );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPageBeforeConflictDiff
	 *
	 * @param EditPage $editPage
	 * @param OutputPage $outputPage
	 */
	public static function onEditPageBeforeConflictDiff(
		EditPage $editPage,
		OutputPage $outputPage
	) {
		$request = $editPage->getContext()->getRequest();

		if ( ExtensionRegistry::getInstance()->isLoaded( 'EventLogging' ) ) {
			$user = $outputPage->getUser();
			$baseRevision = $editPage->getBaseRevision();
			$latestRevision = $editPage->getArticle()->getRevision();

			// TODO: implement complexity metrics
			$conflictChunks = 0;
			$conflictChars = 0;

			// https://meta.wikimedia.org/w/index.php?title=Schema:TwoColConflictConflict&oldid=19872073
			\EventLogging::logEvent(
				'TwoColConflictConflict',
				19872073,
				[
					'twoColConflictShown' => self::shouldTwoColConflictBeShown( $user ),
					'isAnon' => $user->isAnon(),
					'editCount' => (int)$user->getEditCount(),
					'pageNs' => $editPage->getTitle()->getNamespace(),
					'baseRevisionId' => $baseRevision ? $baseRevision->getId() : 0,
					'latestRevisionId' => $latestRevision ? $latestRevision->getId() : 0,
					'textUser' => $editPage->textbox2,
					'summary' => $editPage->summary,
					'conflictChunks' => $conflictChunks,
					'conflictChars' => $conflictChars,
					'startTime' => $editPage->starttime,
					'editTime' => $editPage->edittime,
					'pageTitle' => $editPage->getTitle()->getText(),
					'hasJavascript' => $request->getBool( 'mw-twocolconflict-js' )
						|| $request->getBool( 'veswitched' ),
				]
			);
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPageBeforeEditButtons
	 *
	 * @param EditPage $editPage
	 * @param array &$buttons
	 * @param int &$tabindex
	 */
	public static function onEditPageBeforeEditButtons(
		EditPage $editPage,
		array &$buttons,
		&$tabindex
	) {
		if ( self::shouldTwoColConflictBeShown( $editPage->getContext()->getUser() ) &&
			!( $editPage instanceof TwoColConflictTestEditPage ) &&
			$editPage->isConflict === true
		) {
			unset( $buttons['diff'] );
			// T230152
			if ( isset( $buttons['preview'] ) ) {
				$buttons['preview']->setDisabled( true );
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetBetaFeaturePreferences
	 *
	 * @param User $user
	 * @param array[] &$prefs
	 */
	public static function onGetBetaFeaturePreferences( $user, array &$prefs ) {
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
	 * @param User $user
	 * @param array[] &$preferences
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		if ( $config->get( 'TwoColConflictBetaFeature' ) &&
			ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' )
		) {
			return;
		}

		$preferences[self::OPTOUT_PREFERENCE_NAME] = [
			'type' => 'toggle',
			'label-message' => 'twocolconflict-preference-enabled',
			'section' => 'editing/advancedediting',
		];
	}

	/**
	 * Called whenever a user wants to reset their preferences.
	 *
	 * @param array &$defaultOptions
	 */
	public static function onUserGetDefaultOptions( array &$defaultOptions ) {
		$defaultOptions[self::OPTOUT_PREFERENCE_NAME] = 1;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderTestModules
	 * @codeCoverageIgnore
	 *
	 * @param array[] &$testModules
	 * @param \ResourceLoader $resourceLoader
	 */
	public static function onResourceLoaderTestModules( array &$testModules, $resourceLoader ) {
		$testModules['qunit']['ext.TwoColConflict.tests'] = [
			'scripts' => [
				'tests/qunit/SplitTwoColConflict/TwoColConflict.Merger.test.js'
			],
			'dependencies' => [
				'ext.TwoColConflict.Split.Merger'
			],
			'localBasePath' => dirname( __DIR__ ),
			'remoteExtPath' => 'TwoColConflict',
		];
	}

}
