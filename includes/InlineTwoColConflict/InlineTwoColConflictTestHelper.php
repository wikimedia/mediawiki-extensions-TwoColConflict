<?php

namespace TwoColConflict\InlineTwoColConflict;

use Html;
use TwoColConflict\RandomChangesGenerator;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class InlineTwoColConflictTestHelper extends InlineTwoColConflictHelper {

	/** @var string */
	private $conflictingTestText;

	/**
	 * Generate text for mocked conflicting revision text
	 */
	public function setUpConflictingTestText() {
		$this->conflictingTestText = RandomChangesGenerator::generateRandomlyChangedText(
			$this->storedversion
		);
	}

	/**
	 * @param string $yourtext
	 * @param string $storedversion
	 */
	public function setTextboxes( $yourtext, $storedversion ) {
		parent::setTextboxes( $yourtext, $storedversion );
		$this->setUpConflictingTestText();
	}

	/**
	 * Do not log conflicts in the test mode
	 */
	public function incrementConflictStats() {
	}

	/**
	 * Build HTML that will be added before the default edit form.
	 *
	 * @return string
	 */
	public function getEditFormHtmlBeforeContent() {
		$this->out->addJsConfigVars(
			'wgTwoColConflictTestMode',
			true
		);
		return parent::getEditFormHtmlBeforeContent();
	}

	/**
	 * Build HTML for the hidden field with the text the user submitted.
	 *
	 * @return string
	 */
	protected function buildRawTextsHiddenFields() {
		return Html::input( 'mw-twocolconflict-your-text', $this->yourtext, 'hidden' ) .
			Html::input( 'mw-twocolconflict-current-text', $this->conflictingTestText, 'hidden' );
	}

	/**
	 * Get unified diff from the conflicting texts
	 *
	 * @return array[]
	 */
	protected function getUnifiedDiff() {
		$currentLines = explode( "\n", $this->conflictingTestText );
		$yourLines = explode( "\n", str_replace( "\r\n", "\n", $this->yourtext ) );

		return parent::getLineBasedUnifiedDiff( $currentLines, $yourLines );
	}

	/**
	 * Build HTML for Edit Summary.
	 *
	 * @return string
	 */
	protected function buildEditSummary() {
		$out = '<div class="mw-twocolconflict-edit-summary">';
		$out .= '<a href="#" class="mw-userlink"><bdi>' .
			$this->getLastUserText() .
			'</bdi></a>';
		$out .= $this->out->getLanguage()->getDirMark();
		$out .= ' <span class="comment">(' .
			$this->out->msg( 'twocolconflict-test-summary-text' ) .
			')</span>';
		$out .= '</div>';

		return $out;
	}

	/**
	 * @return string
	 */
	protected function getLastUserText() {
		return $this->out->msg( 'twocolconflict-test-username' );
	}

}
