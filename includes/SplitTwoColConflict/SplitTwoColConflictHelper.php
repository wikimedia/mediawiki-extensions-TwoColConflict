<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use MediaWiki\Content\IContentHandlerFactory;
use MediaWiki\EditPage\TextConflictHelper;
use OutputPage;
use Title;
use TwoColConflict\AnnotatedHtmlDiffFormatter;
use User;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class SplitTwoColConflictHelper extends TextConflictHelper {

	/**
	 * @var string
	 */
	private $newEditSummary;

	/**
	 * @var ResolutionSuggester
	 */
	private $resolutionSuggester;

	/**
	 * @param Title $title
	 * @param OutputPage $out
	 * @param \IBufferingStatsdDataFactory $stats
	 * @param string $submitLabel
	 * @param string $newEditSummary
	 * @param IContentHandlerFactory $contentHandlerFactory
	 * @param ResolutionSuggester $resolutionSuggester
	 *
	 * @throws \MWUnknownContentModelException
	 */
	public function __construct(
		Title $title,
		OutputPage $out,
		\IBufferingStatsdDataFactory $stats,
		string $submitLabel,
		string $newEditSummary,
		IContentHandlerFactory $contentHandlerFactory,
		ResolutionSuggester $resolutionSuggester
	) {
		parent::__construct( $title, $out, $stats, $submitLabel, $contentHandlerFactory );

		$this->newEditSummary = $newEditSummary;
		$this->resolutionSuggester = $resolutionSuggester;

		$this->out->enableOOUI();
		$this->out->addModuleStyles( [
			'oojs-ui.styles.icons-editing-core',
			'oojs-ui.styles.icons-movement'
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function incrementConflictStats( User $user = null ) {
		parent::incrementConflictStats( $user );
		$this->stats->increment( 'TwoColConflict.conflict' );
		// XXX This is copied directly from core and we may be able to refactor something here.
		// Only include 'standard' namespaces to avoid creating unknown numbers of statsd metrics
		if (
			$this->title->getNamespace() >= NS_MAIN &&
			$this->title->getNamespace() <= NS_CATEGORY_TALK
		) {
			$this->stats->increment(
				'TwoColConflict.conflict.byNamespaceId.' . $this->title->getNamespace()
			);
		}
		if ( $user ) {
			$this->incrementStatsByUserEdits( $user->getEditCount(), 'TwoColConflict.conflict' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function incrementResolvedStats( User $user = null ) {
		parent::incrementResolvedStats( $user );
		$this->stats->increment( 'TwoColConflict.conflict.resolved' );
		// XXX This is copied directly from core and we may be able to refactor something here.
		// Only include 'standard' namespaces to avoid creating unknown numbers of statsd metrics
		if (
			$this->title->getNamespace() >= NS_MAIN &&
			$this->title->getNamespace() <= NS_CATEGORY_TALK
		) {
			$this->stats->increment(
				'TwoColConflict.conflict.resolved.byNamespaceId.' . $this->title->getNamespace()
			);
		}
		if ( $user ) {
			$this->incrementStatsByUserEdits(
				$user->getEditCount(), 'TwoColConflict.conflict.resolved'
			);
		}
	}

	/**
	 * Replace default header for explaining the conflict screen.
	 *
	 * @return string
	 */
	public function getExplainHeader() {
		// TODO
		return '';
	}

	/**
	 * Shows the diff part in the original conflict handling. Is not
	 * used and overwritten by a simple container for the result text.
	 *
	 * @param array $customAttribs
	 *
	 * @return string HTML
	 */
	public function getEditConflictMainTextBox( array $customAttribs = [] ) {
		return '';
	}

	/**
	 * Shows the diff part in the original conflict handling. Is not
	 * used and overwritten.
	 */
	public function showEditFormTextAfterFooters() {
	}

	/**
	 * Build HTML that will be added before the default edit form.
	 *
	 * @return string
	 */
	public function getEditFormHtmlBeforeContent() {
		$storedLines = SplitConflictUtils::splitText( $this->storedversion );
		$yourLines = SplitConflictUtils::splitText( $this->yourtext );

		$suggestion = $this->resolutionSuggester->getResolutionSuggestion(
			$this->title,
			$storedLines,
			$yourLines
		);
		if ( $suggestion ) {
			$conflictView = $this->buildResolutionSuggestionView( $suggestion );
		} else {
			$conflictView = $this->buildEditConflictView( $storedLines, $yourLines );
		}

		return Html::input( 'wpTextbox1', $this->storedversion, 'hidden' ) .
			Html::input( 'mw-twocolconflict-title', $this->title->getPrefixedText(), 'hidden' ) .
			$conflictView .
			$this->buildRawTextsHiddenFields();
	}

	/**
	 * Build HTML content that will be added after the default edit form.
	 *
	 * @return string
	 */
	public function getEditFormHtmlAfterContent() {
		$this->out->addModuleStyles( 'ext.TwoColConflict.SplitCss' );
		$this->out->addModules( 'ext.TwoColConflict.SplitJs' );
		return '';
	}

	private function getPreSaveTransformedLines() {
		$user = $this->out->getUser();
		$language = $this->out->getLanguage();

		$content = new \WikitextContent( $this->yourtext );
		$parserOptions = new \ParserOptions( $user, $this->out->getLanguage() );
		// @phan-suppress-next-line PhanUndeclaredMethod
		$previewWikitext = $content->preSaveTransform( $this->title, $user, $parserOptions )->getText();
		return SplitConflictUtils::splitText( $previewWikitext );
	}

	/**
	 * Build HTML that will add the textbox with the unified diff.
	 *
	 * @param string[] $storedLines
	 * @param string[] $yourLines
	 *
	 * @return string
	 */
	private function buildEditConflictView( array $storedLines, array $yourLines ) : string {
		$user = $this->out->getUser();
		$language = $this->out->getLanguage();
		$formatter = new AnnotatedHtmlDiffFormatter();
		$diff = $formatter->format( $storedLines, $yourLines, $this->getPreSaveTransformedLines() );

		$out = ( new HtmlSplitConflictHeader(
			$this->title,
			$user,
			$language,
			false,
			$this->newEditSummary
		) )->getHtml();
		$out .= ( new HtmlSplitConflictView(
			$user,
			$language
		) )->getHtml(
			$diff,
			// Note: Can't use getBool() because that discards arrays
			(bool)$this->out->getRequest()->getArray( 'mw-twocolconflict-split-content' )
		);
		return $out;
	}

	private function buildResolutionSuggestionView( $suggestion ) : string {
		// TODO implement interface for the suggested resolution
		return 'Resolution suggestion goes here.';
	}

	/**
	 * Build HTML for the hidden field with the text the user submitted.
	 *
	 * @return string
	 */
	private function buildRawTextsHiddenFields() : string {
		return Html::input( 'mw-twocolconflict-current-text', $this->storedversion, 'hidden' ) .
			Html::input( 'mw-twocolconflict-your-text', $this->yourtext, 'hidden' );
	}

}
