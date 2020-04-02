<?php

namespace TwoColConflict;

use EditPage;
use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use OutputPage;
use TwoColConflict\SpecialConflictTestPage\TwoColConflictTestEditPage;
use TwoColConflict\SplitTwoColConflict\ConflictFormValidator;
use TwoColConflict\SplitTwoColConflict\ResolutionSuggester;
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
		if ( !TwoColConflictContext::shouldTwoColConflictBeShown( $editPage->getContext()->getUser() ) ) {
			return;
		}

		$editPage->setEditConflictHelperFactory( function ( $submitButtonLabel ) use ( $editPage ) {
			$baseRevision = $editPage->getBaseRevision();

			return new SplitTwoColConflictHelper(
				$editPage->getTitle(),
				$editPage->getContext()->getOutput(),
				MediaWikiServices::getInstance()->getStatsdDataFactory(),
				$submitButtonLabel,
				$editPage->summary,
				MediaWikiServices::getInstance()->getContentHandlerFactory(),
				new ResolutionSuggester(
					$baseRevision ? $baseRevision->getRevisionRecord() : null,
					$editPage->getArticle()->getContentHandler()->getDefaultFormat()
				)
			);
		} );

		$request = $editPage->getContext()->getRequest();
		if ( !( new ConflictFormValidator() )->validateRequest( $request ) ) {
			// Mark the conflict as *not* being resolved to trigger it again. This works because
			// EditPage uses editRevId to decide if it's even possible to run into a conflict.
			// If editRevId reflects the most recent revision, it can't be a conflict (again),
			// and the user's input is stored, even if it reverts everything.
			// Warning, this is particularly fragile! This assumes EditPage was not reading the
			// WebRequest values before!
			$request->setVal( 'editRevId', $request->getInt( 'parentRevId' ) );
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

			if ( $request->getBool( 'mw-twocolconflict-single-column-view' )
				&& !( new ConflictFormValidator() )->validateRequest( $request )
			) {
				// When the request is invalid, drop the "other" row to force the original conflict
				// to be re-created, and not silently resolved or corrupted.
				foreach ( $contentRows as $num => &$row ) {
					if ( array_key_exists( 'other', $row ) ) {
						$row['other'] = '';
					}
				}
			}

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

			$conflictChunks = 0;
			$conflictChars = 0;
			if ( $baseRevision && $latestRevision ) {
				// Attempt the automatic merge, to measure the number of actual conflicts.
				/** @var ThreeWayMerge $merge */
				$merge = MediaWikiServices::getInstance()->getService( 'TwoColConflictThreeWayMerge' );
				$result = $merge->merge3(
					$baseRevision->getContent()->serialize(),
					$latestRevision->getContent()->serialize(),
					$editPage->textbox2
				);

				if ( !$result->isCleanMerge() ) {
					$conflictChunks = $result->getOverlappingChunkCount();
					$conflictChars = $result->getOverlappingChunkSize();
				}
			}

			// https://meta.wikimedia.org/w/index.php?title=Schema:TwoColConflictConflict&oldid=19872073
			\EventLogging::logEvent(
				'TwoColConflictConflict',
				19872073,
				[
					'twoColConflictShown' => TwoColConflictContext::shouldTwoColConflictBeShown( $user ),
					'isAnon' => $user->isAnon(),
					'editCount' => (int)$user->getEditCount(),
					'pageNs' => $editPage->getTitle()->getNamespace(),
					'baseRevisionId' => $baseRevision ? $baseRevision->getId() : 0,
					'latestRevisionId' => $latestRevision ? $latestRevision->getId() : 0,
					'textUser' => mb_substr( $editPage->textbox2, 0, 128 ),
					'summary' => mb_substr( $editPage->summary, 0, 128 ),
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
		if ( TwoColConflictContext::shouldTwoColConflictBeShown( $editPage->getContext()->getUser() ) &&
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
	public static function onGetPreferences( $user, array &$preferences ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		if ( $config->get( 'TwoColConflictBetaFeature' ) &&
			ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' )
		) {
			return;
		}

		$preferences[TwoColConflictContext::OPTOUT_PREFERENCE_NAME] = [
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
		$defaultOptions[TwoColConflictContext::OPTOUT_PREFERENCE_NAME] = 1;
	}

}
