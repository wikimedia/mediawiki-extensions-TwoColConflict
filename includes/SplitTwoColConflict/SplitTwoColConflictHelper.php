<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use MediaWiki\EditPage\TextConflictHelper;
use MediaWiki\Revision\RevisionRecord;
use OutputPage;
use Title;
use TwoColConflict\LineBasedUnifiedDiffFormatter;
use UnexpectedValueException;
use WikiPage;

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
	 * @param string $yourtext
	 * @param string $storedversion
	 */
	public function setTextboxes( $yourtext, $storedversion ) {
		$contentRows = $this->out->getRequest()->getArray( 'mw-twocolconflict-split-content' );
		$extraLineFeeds = $this->out->getRequest()->getArray( 'mw-twocolconflict-split-linefeeds' );

		// The incoming $yourtext is already merged, possibly containing paragraphs from both sides.
		// If we can, we restore the users original submission.
		if ( $contentRows && $extraLineFeeds ) {
			$yourtext = SplitConflictMerger::mergeSplitConflictResults(
				$contentRows,
				$extraLineFeeds,
				'your'
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
	 * @return RevisionRecord
	 */
	public function getRevisionRecord() {
		$wikiPage = WikiPage::factory( $this->title );
		/** @see https://phabricator.wikimedia.org/T203085 */
		$wikiPage->loadPageData( 'fromdbmaster' );
		$revision = $wikiPage->getRevision();

		if ( !$revision ) {
			throw new UnexpectedValueException( 'The title "' . $this->title->getPrefixedText() .
				'" does not refer to an existing page' );
		}

		return $revision->getRevisionRecord();
	}

	/**
	 * FIXME: This also looks like it is to generic, and can be replaced with more specific getters
	 *
	 * @return OutputPage
	 */
	public function getOutput() {
		return $this->out;
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
		$this->out->addModuleStyles( [
			'ext.TwoColConflict.SplitCss',
		] );
		$this->out->addModules( [
			'ext.TwoColConflict.SplitJs',
		] );
		return '';
	}

	/**
	 * Build HTML that will add the textbox with the unified diff.
	 *
	 * @return string
	 */
	private function buildEditConflictView() {
		$unifiedDiff = $this->getLineBasedUnifiedDiff();

		$out = ( new HtmlSplitConflictHeader(
			$this->getRevisionRecord(),
			$this->getOutput()->getUser(),
			$this->getOutput()->getLanguage(),
			false,
			$this->newEditSummary
		) )->getHtml();
		$out .= ( new HtmlSplitConflictView(
			$this->out->getUser(),
			$this->out->getLanguage(),
			$this->out->getRequest()->getArray( 'mw-twocolconflict-side-selector' ) ?: []
		) )->getHtml(
			$unifiedDiff,
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
		$formatter->insClass = ' class="mw-twocolconflict-diffchange"';
		$formatter->delClass = ' class="mw-twocolconflict-diffchange"';
		return $formatter->format(
			new \Diff( $this->storedLines, $this->yourLines )
		);
	}

}
