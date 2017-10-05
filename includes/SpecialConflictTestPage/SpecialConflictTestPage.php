<?php

use MediaWiki\MediaWikiServices;

class SpecialConflictTestPage extends SpecialPage {

	/**
	 * @var Config $config
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
			return;
		}

		$this->getOutput()->enableOOUI();
		$this->addModules();
		$request = $this->getRequest();

		if ( $request->getVal( 'wpPreview' ) != null || $request->getVal( 'wpDiff' ) != null ) {
			$this->showHintBoxRaw( ( new Message( 'twoColConflict-test-preview-hint' ) )->parse() );

			$title = Title::newFromText( $request->getVal( 'mw-twocolconflict-title' ) );
			$this->showPreview( $title, $request->getVal( 'wpTextbox1' ) );
			return;
		}

		$testTitleText = $request->getVal( 'mw-twocolconflict-test-title' );
		if ( $testTitleText === null ) {
			$this->showHintBox( ( new Message( 'twoColConflict-test-initial-hint' ) )->parse() );

			$this->showLoadTitle();
			return;
		}

		$testTitle = Title::newFromText( $testTitleText );
		if ( $testTitle === null || !$testTitle->exists() ) {
			$this->showHintBox( ( new Message( 'twoColConflict-test-initial-hint' ) )->parse() );

			$this->showWarningBox( new Message( 'twoColConflict-test-title-not-existing' ) );
			$this->showLoadTitle();
			return;
		}

		$testArticle = Article::newFromTitle( $testTitle, $this->getContext() );
		if ( $request->getVal( 'mw-twocolconflict-test-text' ) === null ) {
			$this->showHintBox( ( new Message( 'twoColConflict-test-edit-hint' ) )->parse() );

			$this->showChangeText(
				$testArticle->getPage()->getContent()->serialize(),
				$testTitle->getPrefixedText()
			);
			return;
		}

		$this->showHintBox(
			( new Message( 'twoColConflict-test-conflict-hint' ) )->parse(),
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
		$this->getOutput()->addHTML( ( new HtmlSpecialTestTitleForm( $this ) )->getHtml() );
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

	/**
	 * @param Article $article
	 */
	private function showConflict( $article ) {
		$twoColConflictTestPage = new TwoColConflictTestPage( $article );
		$twoColConflictTestPage->setUpConflictingTestText();
		$twoColConflictTestPage->setUpFakeConflictRequest();
		$twoColConflictTestPage->edit();
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
			class_exists( BetaFeatures::class ) &&
			!BetaFeatures::isFeatureEnabled( $this->getContext()->getUser(), 'twocolconflict' )
		) {
			return false;
		}

		return true;
	}
}
