<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use MediaWiki\EditPage\TextConflictHelper;
use OutputPage;
use Title;
use TwoColConflict\LineBasedUnifiedDiffFormatter;
use WikiPage;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class SplitTwoColConflictHelper extends TextConflictHelper {

	/**
	 * @var WikiPage
	 */
	private $wikiPage;

	/**
	 * @var string[]
	 */
	private $yourLines;

	/**
	 * @var string[]
	 */
	private $storedLines;

	/**
	 * @inheritDoc
	 */
	public function __construct(
		Title $title,
		OutputPage $out,
		\IBufferingStatsdDataFactory $stats,
		$submitLabel
	) {
		parent::__construct( $title, $out, $stats, $submitLabel );
		$this->wikiPage = WikiPage::factory( $title );
		$this->out->enableOOUI();
		$this->getOutput()->addModuleStyles( [ 'oojs-ui.styles.icons-editing-core' ] );
	}

	/**
	 * @param string $yourtext
	 * @param string $storedversion
	 */
	public function setTextboxes( $yourtext, $storedversion ) {
		parent::setTextboxes(
			$yourtext,
			$storedversion
		);

		$this->yourLines = $this->splitText( $this->yourtext );
		$this->storedLines = $this->splitText( $this->storedversion );
	}

	/**
	 * @param string $text
	 *
	 * @return string[]
	 */
	private function splitText( $text ) {
		return preg_split( '/\r*\n(?![\r\n])/', $text );
	}

	/**
	 * FIXME: The (currently) only 2 callers of this don't need a WikiPage object, but a Revision
	 * object. Only this should be returned.
	 *
	 * @return WikiPage
	 */
	public function getWikiPage() {
		return $this->wikiPage;
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
			$this->wikiPage->getTitle()->getText(), 'hidden'
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
		$out = ( new HtmlSplitConflictHeader( $this ) )->getHtml();
		$out .= ( new HtmlSplitConflictView(
			$this->out->getUser(),
			$this->out->getLanguage()
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
