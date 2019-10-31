<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
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
	 * @var string[]
	 */
	private $yourLines;

	/**
	 * @var string[]
	 */
	private $storedLines;

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
	 */
	public function __construct(
		Title $title,
		OutputPage $out,
		\IBufferingStatsdDataFactory $stats,
		$submitLabel,
		$newEditSummary
	) {
		parent::__construct( $title, $out, $stats, $submitLabel );

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
	 * @param string $yourtext
	 * @param string $storedversion
	 */
	public function setTextboxes( $yourtext, $storedversion ) {
		$request = $this->out->getRequest();
		$contentRows = $request->getArray( 'mw-twocolconflict-split-content' );
		$extraLineFeeds = $request->getArray( 'mw-twocolconflict-split-linefeeds' );

		// The incoming $yourtext is already merged, possibly containing paragraphs from both sides.
		// If we can, we restore the users original submission.
		if ( $contentRows && $extraLineFeeds ) {
			$yourtext = SplitConflictMerger::mergeSplitConflictResults(
				$contentRows,
				$extraLineFeeds,
				'your'
			);
			$storedversion = SplitConflictMerger::mergeSplitConflictResults(
				$contentRows,
				$extraLineFeeds,
				'other'
			);
		}

		$this->yourLines = $this->splitText( $yourtext );
		$this->storedLines = $this->splitText( $storedversion );

		parent::setTextboxes( $yourtext, $storedversion );
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
		$out = Html::input( 'wpTextbox1', $this->storedversion, 'hidden' );
		$out .= Html::input( 'mw-twocolconflict-submit', 'true', 'hidden' );
		$out .= Html::input(
			'mw-twocolconflict-title',
			$this->title->getText(), 'hidden'
		);
		$out .= $this->buildEditConflictView();
		$out .= $this->buildRawTextsHiddenFields();
		return $out;
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
	private function buildEditConflictView() {
		$user = $this->out->getUser();
		$language = $this->out->getLanguage();

		$out = ( new HtmlSplitConflictHeader(
			$this->title,
			$user,
			$language,
			false,
			$this->newEditSummary
		) )->getHtml();
		$out .= ( new HtmlSplitConflictView(
			$user,
			$language,
			$this->out->getRequest()->getArray( 'mw-twocolconflict-side-selector' ) ?: []
		) )->getHtml(
			$this->getLineBasedUnifiedDiff(),
			$this->yourLines,
			$this->storedLines
		);
		return $out;
	}

	/**
	 * Build HTML for the hidden field with the text the user submitted.
	 *
	 * @return string
	 */
	private function buildRawTextsHiddenFields() {
		return Html::input( 'mw-twocolconflict-current-text', $this->storedversion, 'hidden' ) .
			Html::input( 'mw-twocolconflict-your-text', $this->yourtext, 'hidden' );
	}

	/**
	 * Get array with line based diff changes.
	 *
	 * @return array[]
	 */
	private function getLineBasedUnifiedDiff() {
		$formatter = new LineBasedUnifiedDiffFormatter();

		return $formatter->format(
			new \Diff( $this->storedLines, $this->yourLines )
		);
	}

}
