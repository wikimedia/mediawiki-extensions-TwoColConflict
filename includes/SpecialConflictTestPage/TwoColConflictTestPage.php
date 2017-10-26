<?php

/**
 * @license GNU GPL v2+
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictTestPage extends TwoColConflictPage {

	/** @var string */
	private $conflictingTestText;

	/**
	 * Generate text for mocked conflicting revision text
	 */
	public function setUpConflictingTestText() {
		$currentText = $this->toEditText( $this->getCurrentContent() );
		$this->conflictingTestText = $this->generateFakeConflictingText( $currentText );
	}

	/**
	 * Mock request parameters need to fake the conflict
	 */
	public function setUpFakeConflictRequest() {
		$request = $this->getContext()->getRequest();
		$request->setVal( 'wpTextbox1', $request->getVal( 'mw-twocolconflict-test-text' ) );
		$request->setVal( 'wpUltimateParam', '1' );
	}

	/**
	 * Do not log conflicts in the test mode
	 */
	protected function incrementConflictStats() {
	}

	/**
	 * Do not add the page specific edit notices on the simulated conflict view
	 */
	protected function addEditNotices() {
	}

	/**
	 * Attempt submission - is overwritten in case of a test conflict to avoid any real save
	 * this will also avoid running the hook that triggers logging a resolved conflict
	 * @param array|bool &$resultDetails See docs for $result in internalAttemptSave
	 * @return Status The resulting status object.
	 */
	public function attemptSave( &$resultDetails = false ) {
		return new Status();
	}

	/**
	 * Set the HTML to encapsulate the default edit form - enforce conflict mode.
	 *
	 * @param callable|null $formCallback That takes an OutputPage parameter; will be called
	 *     during form output near the top, for captchas and the like.
	 */
	public function showEditForm( $formCallback = null ) {
		$this->isConflict = true;
		$this->context->getOutput()->addJsConfigVars(
			'wgTwoColConflictTestMode',
			true
		);
		parent::showEditForm( $formCallback );
	}

	/**
	 * Returns an array of html code of the following buttons:
	 * save, diff and preview
	 *
	 * @param int &$tabindex Current tabindex
	 * @return array
	 */
	public function getEditButtons( &$tabindex ) {
		$buttons = [];
		$buttons['preview'] = new OOUI\ButtonInputWidget( [
				'id' => 'wpTestPreviewWidget',
				'name' => 'wpPreview',
				'tabindex' => ++$tabindex,
				'inputId' => 'wpPreview',
				'useInputTag' => true,
				'flags' => [ 'constructive', 'primary' ],
				'label' => $this->context->msg( 'twoColConflict-test-preview-submit' )->text(),
				'infusable' => true,
				'type' => 'submit',
				'title' => Linker::titleAttrib( 'preview' ),
				'accessKey' => Linker::accesskey( 'preview' ),
			] );

		return $buttons;
	}

	/**
	 * Build HTML for the hidden field with the text the user submitted.
	 *
	 * @return string
	 */
	protected function buildRawTextsHiddenFields() {
		$editableYourVersionText = $this->toEditText( $this->textbox1 );
		$editableCurrentVersionText = $this->conflictingTestText;

		return Html::input( 'mw-twocolconflict-your-text', $editableYourVersionText, 'hidden' ) .
			Html::input( 'mw-twocolconflict-current-text', $editableCurrentVersionText, 'hidden' );
	}

	/**
	 * Get unified diff from the conflicting texts
	 *
	 * @return array[]
	 */
	protected function getUnifiedDiff() {
		$currentText = $this->conflictingTestText;
		$yourText = $this->textbox1;

		$currentLines = explode( "\n", $currentText );
		$yourLines = explode( "\n", str_replace( "\r\n", "\n", $yourText ) );

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
		$out .= $this->getContext()->getLanguage()->getDirMark();
		$out .= ' <span class="comment">(' .
			$this->getContext()->msg( 'twoColConflict-test-summary-text' ) .
			')</span>';
		$out .= '</div>';

		return $out;
	}

	/**
	 * @return string
	 */
	protected function getLastUserText() {
		return $this->getContext()->msg( 'twoColConflict-test-username' );
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
