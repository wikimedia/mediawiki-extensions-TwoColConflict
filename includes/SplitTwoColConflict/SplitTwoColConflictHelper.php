<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use MediaWiki\Content\IContentHandlerFactory;
use MediaWiki\EditPage\TextConflictHelper;
use OutputPage;
use Title;
use TwoColConflict\LineBasedUnifiedDiffFormatter;
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
	 * @param Title $title
	 * @param OutputPage $out
	 * @param \IBufferingStatsdDataFactory $stats
	 * @param string $submitLabel
	 * @param string $newEditSummary
	 * @param IContentHandlerFactory $contentHandlerFactory
	 *
	 * @throws \MWUnknownContentModelException
	 */
	public function __construct(
		Title $title,
		OutputPage $out,
		\IBufferingStatsdDataFactory $stats,
		string $submitLabel,
		string $newEditSummary,
		IContentHandlerFactory $contentHandlerFactory
	) {
		parent::__construct( $title, $out, $stats, $submitLabel, $contentHandlerFactory );

		$this->newEditSummary = $newEditSummary;

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
	 * @param string $text
	 *
	 * @return string[]
	 */
	private function splitText( $text ) {
		return preg_split( '/\n(?!\n)/',
			str_replace( "\r\n", "\n", $text )
		);
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
		return Html::input( 'wpTextbox1', $this->storedversion, 'hidden' ) .
			Html::input( 'mw-twocolconflict-submit', '1', 'hidden' ) .
			Html::input( 'mw-twocolconflict-title', $this->title->getPrefixedText(), 'hidden' ) .
			$this->buildEditConflictView() .
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

	/**
	 * Build HTML that will add the textbox with the unified diff.
	 *
	 * @return string
	 */
	private function buildEditConflictView() : string {
		$user = $this->out->getUser();
		$language = $this->out->getLanguage();
		$storedLines = $this->splitText( $this->storedversion );
		$yourLines = $this->splitText( $this->yourtext );

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
			$this->getLineBasedUnifiedDiff( $storedLines, $yourLines ),
			$yourLines,
			$storedLines
		);
		return $out;
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

	/**
	 * Get array with line based diff changes.
	 *
	 * @param string[] $fromLines
	 * @param string[] $toLines
	 *
	 * @return array[]
	 */
	private function getLineBasedUnifiedDiff( array $fromLines, array $toLines ) : array {
		$formatter = new LineBasedUnifiedDiffFormatter();
		return $formatter->format( new \Diff( $fromLines, $toLines ) );
	}

}
