<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use MediaWiki\EditPage\TextConflictHelper;
use OutputPage;
use Title;
use TwoColConflict\LineBasedUnifiedDiffFormatter;
use WikiPage;

class SplitTwoColConflictHelper extends TextConflictHelper {

	/**
	 * @var WikiPage
	 */
	private $wikiPage;

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
	}

	/**
	 * @return WikiPage
	 */
	public function getWikiPage() {
		return $this->wikiPage;
	}

	/**
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
	 * @param mixed[]|null $customAttribs
	 * @return string HTML
	 */
	public function getEditConflictMainTextBox( $customAttribs = [] ) {
		return '';
	}

	/**
	 * Shows the diff part in the original conflict handling. Is not
	 * used and overwritten.
	 */
	public function showEditFormTextAfterFooters() {
		return;
	}

	/**
	 * Build HTML that will be added before the default edit form.
	 *
	 * @return string
	 */
	public function getEditFormHtmlBeforeContent() {
		$out = Html::input( 'mw-twocolconflict-submit', 'true', 'hidden' );
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
		$unifiedDiff = $this->getUnifiedDiff();
		$out = ( new HtmlSplitConflictHeader( $this ) )->getHtml();
		$out .= ( new HtmlSplitConflictView() )->getHtml( $unifiedDiff );
		return $out;
	}

	/**
	 * Build HTML for the hidden field with the text the user submitted.
	 *
	 * @return string
	 */
	protected function buildRawTextsHiddenFields() {
		return Html::input( 'mw-twocolconflict-current-text', $this->storedversion, 'hidden' ) .
			Html::input( 'mw-twocolconflict-your-text', $this->yourtext, 'hidden' );
	}

	/**
	 * Get unified diff from the conflicting texts
	 *
	 * @return array[]
	 */
	protected function getUnifiedDiff() {
		$currentLines = explode( "\n", $this->storedversion );
		$yourLines = explode( "\n", str_replace( "\r\n", "\n", $this->yourtext ) );
		return $this->getLineBasedUnifiedDiff( $currentLines, $yourLines );
	}

	/**
	 * Get array with line based diff changes.
	 *
	 * @param string[] $fromTextLines
	 * @param string[] $toTextLines
	 * @return array[]
	 */
	protected function getLineBasedUnifiedDiff( $fromTextLines, $toTextLines ) {
		$formatter = new LineBasedUnifiedDiffFormatter();
		$formatter->insClass = ' class="mw-twocolconflict-diffchange"';
		$formatter->delClass = ' class="mw-twocolconflict-diffchange"';
		return $formatter->format(
			new \Diff( $fromTextLines, $toTextLines )
		);
	}

}
