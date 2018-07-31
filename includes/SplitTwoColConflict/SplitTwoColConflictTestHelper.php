<?php

namespace TwoColConflict\SplitTwoColConflict;

use TwoColConflict\RandomChangesGenerator;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SplitTwoColConflictTestHelper extends SplitTwoColConflictHelper {

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
	 * Get unified diff from the conflicting texts
	 *
	 * @return array[]
	 */
	protected function getUnifiedDiff() {
		$currentLines = explode( "\n", $this->conflictingTestText );
		$yourLines = explode( "\n", str_replace( "\r\n", "\n", $this->yourtext ) );

		return parent::getLineBasedUnifiedDiff( $currentLines, $yourLines );
	}

}
