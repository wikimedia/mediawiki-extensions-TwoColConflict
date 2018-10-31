<?php

namespace TwoColConflict\SpecialConflictTestPage;

use Article;
use Html;
use MediaWiki\MediaWikiServices;
use Message;
use SpecialPage;
use Title;
use TwoColConflict\InlineTwoColConflict\InlineTwoColConflictTestHelper;
use TwoColConflict\SplitTwoColConflict\SplitConflictMerger;
use TwoColConflict\SplitTwoColConflict\SplitTwoColConflictTestHelper;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SpecialConflictTestPage extends SpecialPage {

	/**
	 * @var \Config
	 */
	public $config;

	public function __construct() {
		parent::__construct( 'SimulateTwoColEditConflict', '', false );
		$this->config = MediaWikiServices::getInstance()->getMainConfig();
	}

	/**
	 * @param null|string $subPage
	 */
	public function execute( $subPage ) {
		if ( !$this->isInBetaAndEnabled() ) {
			$this->showWarningBox( ( new Message( 'twocolconflict-test-needsbeta' ) )->parse() );
			return;
		}

		$this->getOutput()->enableOOUI();
		$this->addModules();
		$this->getOutput()->setPageTitle( new Message( 'twocolconflict-test-page-title' ) );
		$request = $this->getRequest();

		if ( $request->getVal( 'wpPreview' ) != null || $request->getVal( 'wpDiff' ) != null ) {
			$this->showHintBoxRaw( ( new Message( 'twocolconflict-test-preview-hint' ) )->parse() );

			$title = Title::newFromText( $request->getVal( 'mw-twocolconflict-title' ) );

			if ( $this->config->get( 'TwoColConflictUseInline' ) ) {
				$this->showPreview( $title, $request->getVal( 'wpTextbox1' ) );
				return;
			}

			$this->showPreview(
				$title,
				SplitConflictMerger::mergeSplitConflictResults(
					$request->getArray( 'mw-twocolconflict-split-content' ),
					$request->getArray( 'mw-twocolconflict-split-linefeeds' ),
					$request->getArray( 'mw-twocolconflict-side-selector' )
				)
			);

			return;
		}

		$testTitleText = $request->getVal( 'mw-twocolconflict-test-title' );
		if ( $testTitleText === null ) {
			$this->showHintBox( ( new Message( 'twocolconflict-test-initial-hint' ) )->parse() );

			$this->showLoadTitle();
			return;
		}

		$testTitle = Title::newFromText( $testTitleText );
		if ( $testTitle === null || !$testTitle->exists() ) {
			$this->showHintBox( ( new Message( 'twocolconflict-test-initial-hint' ) )->parse() );

			$this->showWarningBox( new Message( 'twocolconflict-test-title-not-existing' ) );
			$this->showLoadTitle();
			return;
		}

		$testArticle = Article::newFromTitle( $testTitle, $this->getContext() );

		if ( !$testArticle->getContentHandler()->supportsDirectEditing() ) {
			$this->showHintBox( ( new Message( 'twocolconflict-test-initial-hint' ) )->parse() );

			$this->showWarningBox( new Message( 'twocolconflict-test-no-direct-editing' ) );
			$this->showLoadTitle();
			return;
		}

		if ( $request->getVal( 'mw-twocolconflict-test-text' ) === null ) {
			$this->showHintBox( ( new Message( 'twocolconflict-test-edit-hint' ) )->parse() );

			$this->showChangeText(
				$testArticle->getPage()->getContent()->serialize(),
				$testTitle->getPrefixedText()
			);
			return;
		}

		$this->showHintBox(
			( new Message( 'twocolconflict-test-conflict-hint' ) )->parse(),
			'mw-twocolconflict-test-conflict-hint'
		);

		$this->showConflict( $testArticle );
	}

	/**
	 * @param string $message
	 * @param string $additionalClass
	 */
	private function showHintBox( $message, $additionalClass = '' ) {
		$this->showHintBoxRaw( Html::rawElement( 'p', [], $message ), $additionalClass );
	}

	private function showHintBoxRaw( $message, $additionalClass = '' ) {
		$this->getOutput()->addHTML(
			Html::rawElement(
				'div',
				[ 'class' => 'mw-twocolconflict-test-hintbox ' . $additionalClass ],
				$message
			)
		);
	}

	/**
	 * @param string $message
	 */
	private function showWarningBox( $message ) {
		$this->getOutput()->addHTML(
			Html::rawElement(
				'div',
				[ 'class' => 'warningbox' ],
				Html::element( 'p', [], $message )
			)
		);
	}

	private function showLoadTitle() {
		$this->getOutput()->addHTML( ( new HtmlSpecialTestTitleForm( $this ) )->getHtml(
			$this->getPresetPage()
		) );
	}

	/**
	 * @param string $baseVersionText
	 * @param string $titleText
	 */
	private function showChangeText( $baseVersionText, $titleText ) {
		$this->getOutput()->addHTML( ( new HtmlSpecialTestTextForm( $this ) )->getHtml(
			$baseVersionText,
			$titleText
		) );
	}

	private function showConflict( Article $article ) {
		$editConflictHelperFactory = function ( $submitButtonLabel ) use ( $article ) {
			if ( $this->config->get( 'TwoColConflictUseInline' ) ) {
				return new InlineTwoColConflictTestHelper(
					$article->getTitle(),
					$article->getContext()->getOutput(),
					MediaWikiServices::getInstance()->getStatsdDataFactory(),
					$submitButtonLabel
				);
			} else {
				return new SplitTwoColConflictTestHelper(
					$article->getTitle(),
					$article->getContext()->getOutput(),
					MediaWikiServices::getInstance()->getStatsdDataFactory(),
					$submitButtonLabel,
					''
				);
			}
		};

		( new TwoColConflictTestEditPage(
			$article,
			$this->getPageTitle(),
			$editConflictHelperFactory
		) )->edit();

		// overwrite title set by EditPage
		$this->getOutput()->setPageTitle( new Message( 'twocolconflict-test-page-title' ) );
	}

	/**
	 * @param Title $title
	 * @param string $wikiText
	 */
	private function showPreview( $title, $wikiText ) {
		$this->getOutput()->addHTML( ( new HtmlPreview( $this ) )->getHtml(
			$title,
			$wikiText
		) );
	}

	private function addModules() {
		$this->getOutput()->addModuleStyles( [
			'ext.TwoColConflict.SpecialConflictTestPageCss',
		] );
	}

	private function isInBetaAndEnabled() {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		/**
		 * If this extension is configured to be a beta feature, and the BetaFeatures extension
		 * is loaded then require the current user to have the feature enabled.
		 */
		if (
			$config->get( 'TwoColConflictBetaFeature' ) &&
			\ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' ) &&
			!\BetaFeatures::isFeatureEnabled( $this->getContext()->getUser(), 'twocolconflict' )
		) {
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	private function getPresetPage() {
		$dbName = MediaWikiServices::getInstance()->getMainConfig()->get( 'DBname' );
		$defaults = $this->testSiteDefaults();

		if ( !isset( $defaults[ $dbName ] ) ) {
			return '';
		}

		return $defaults[ $dbName ];
	}

	private function testSiteDefaults() {
		return [
			'testwiki' => 'Page023',
			'metawiki' => 'WMDE_Technical_Wishes/Edit_Conflicts',
			'mediawikiwiki' => 'Help:Two_Column_Edit_Conflict_View',
			'enwiki' => 'Wild_goat',
			'dewiki' => 'Hausziege',
			'eswiki' => 'Ammotragus_lervia',
			'jawiki' => 'ヤギ',
			'frwiki' => 'Mouflon_à_manchettes',
			'ruwiki' => 'Гривистый_баран',
			'itwiki' => 'Ammotragus_lervia',
			'zhwiki' => '羊亚科',
			'plwiki' => 'Arui_grzywiasta',
			'ptwiki' => 'Capra_aegagrus_hircus',
			'hewiki' => 'עז_הבית',
			'arwiki' => 'ماعز',
		];
	}

}
