<?php

/**
 * @license GNU GPL v2+
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class InlineTwoColConflictTestHelper extends InlineTwoColConflictHelper {

	/** @var string */
	private $conflictingTestText;

	/**
	 * Generate text for mocked conflicting revision text
	 */
	public function setUpConflictingTestText() {
		$this->conflictingTestText = $this->generateFakeConflictingText( $this->storedversion );
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
		$out .= '<a href="#" class="mw-userlink" onclick="_blank"><bdi>' .
			$this->getLastUserText() .
			'</bdi></a>';
		$out .= $this->out->getLanguage()->getDirMark();
		$out .= ' <span class="comment">(' .
			$this->out->msg( 'twoColConflict-test-summary-text' ) .
			')</span>';
		$out .= '</div>';

		return $out;
	}

	/**
	 * @return string
	 */
	protected function getLastUserText() {
		return $this->out->msg( 'twoColConflict-test-username' );
	}

	/**
	 * @param string $baseVersionText
	 * @return string
	 */
	private function generateFakeConflictingText( $baseVersionText ) {
		$randomWord = $this->getRandomWord( $baseVersionText, 5 );
		return $this->insertTextAtRandom( $baseVersionText, $randomWord );
	}

	/**
	 * Inserts text to a random place in a text. Text will be inserted in a place where a
	 * contiguous flow of characters or numbers is interrupted by other symbols. See
	 * RegExp \pL and \pN definitions.
	 *
	 * @param string $originalText
	 * @param string $textToInsert
	 * @return string
	 */
	private function insertTextAtRandom( $originalText, $textToInsert ) {
		preg_match_all( '#[^\pL\pN]+#u', $originalText, $spaces, PREG_OFFSET_CAPTURE );
		$match = $spaces[0][ array_rand( $spaces[0] ) ];
		return substr_replace( $originalText, $match[0] . $textToInsert, $match[1], 0 );
	}

	/**
	 * Returns a random group of contiguous characters or numbers greater than a specific length
	 * from a text. See RegExp \pL and \pN definitions.
	 *
	 * @param string $text
	 * @param int $minLength Min length of word. Will fallback to the best fit after 30 attempts.
	 * @return string
	 */
	private function getRandomWord( $text, $minLength ) {
		$randomWord = '';
		$attempts = 0;
		$words = preg_split( '#[^\pL\pN]+#u', $text, -1, PREG_SPLIT_NO_EMPTY );

		while ( mb_strlen( $randomWord ) < $minLength && $attempts < 30 ) {
			$randomWord = $words[ array_rand( $words ) ];
			$attempts++;
		}

		return $randomWord;
	}
}
