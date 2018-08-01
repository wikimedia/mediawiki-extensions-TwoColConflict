<?php

namespace TwoColConflict\SplitTwoColConflict;

use TwoColConflict\RandomChangesGenerator;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SplitTwoColConflictTestHelper extends SplitTwoColConflictHelper {

	/**
	 * Generate a random fake change as currently stored text
	 *
	 * @param string $yourtext
	 * @param string $storedversion
	 */
	public function setTextboxes( $yourtext, $storedversion ) {
		parent::setTextboxes(
			$yourtext,
			RandomChangesGenerator::generateRandomlyChangedText( $storedversion )
		);
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

}
