<?php

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictTestEditPage extends EditPage {

	/**
	 * Setup the request values to provoke a simulated edit conflict
	 */
	public function setUpFakeConflictRequest() {
		$request = $this->getContext()->getRequest();
		$request->setVal( 'wpTextbox1', $request->getVal( 'mw-twocolconflict-test-text' ) );
		$request->setVal( 'wpUltimateParam', '1' );
	}

	/**
	 * Do not add the page specific edit notices on the simulated conflict view
	 */
	protected function addEditNotices() {
	}

	/**
	 * Attempt submission - is overwritten in case of a test conflict to avoid any real save
	 * this will also avoid running the hook that triggers logging a resolved conflict
	 * @param array|bool &$resultDetails
	 * @return Status
	 */
	public function attemptSave( &$resultDetails = false ) {
		$status = Status::newGood();
		$status->setResult( false, self::AS_CONFLICT_DETECTED );
		return $status;
	}

	/**
	 * Force conflict mode
	 * @param callable|null $formCallback
	 */
	public function showEditForm( $formCallback = null ) {
		$this->isConflict = true;
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
			'flags' => [ 'progressive', 'primary' ],
			'label' => $this->context->msg( 'twoColConflict-test-preview-submit' )->text(),
			'infusable' => true,
			'type' => 'submit',
			'title' => Linker::titleAttrib( 'preview' ),
			'accessKey' => Linker::accesskey( 'preview' ),
		] );

		return $buttons;
	}
}
