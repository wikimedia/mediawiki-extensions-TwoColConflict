<?php

namespace TwoColConflict\SpecialConflictTestPage;

use Article;
use EditPage;
use Status;
use Title;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictTestEditPage extends EditPage {

	public function __construct(
		Article $article,
		Title $contextTitle,
		callable $editConflictHelperFactory
	) {
		parent::__construct( $article );

		/** @see https://phabricator.wikimedia.org/T176526 */
		$this->setContextTitle( $contextTitle );
		$this->setEditConflictHelperFactory( $editConflictHelperFactory );

		// Setup the request values to provoke a simulated edit conflict
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
	 *
	 * @param array|bool &$resultDetails
	 *
	 * @return Status
	 */
	public function attemptSave( &$resultDetails = false ) {
		$status = Status::newGood();
		$status->setResult( false, self::AS_CONFLICT_DETECTED );
		return $status;
	}

	/**
	 * Force conflict mode
	 *
	 * @param callable|null $formCallback
	 */
	public function showEditForm( $formCallback = null ) {
		$this->isConflict = true;
		parent::showEditForm( $formCallback );
	}

	/**
	 * @param int &$tabindex Current tabindex
	 *
	 * @return \OOUI\ButtonInputWidget[] 1-element array with the preview button only
	 */
	public function getEditButtons( &$tabindex ) {
		/** @var \OOUI\ButtonInputWidget[] $buttons */
		$buttons = parent::getEditButtons( $tabindex );

		$label = $this->context->msg( 'twocolconflict-test-preview-submit' )->text();
		$buttons['preview']->setAttributes( [ 'id' => 'wpTestPreviewWidget' ] );
		$buttons['preview']->setFlags( $buttons['save']->getFlags() );
		$buttons['preview']->setLabel( $label );

		// Remove all buttons but the preview button
		return [ 'preview' => $buttons['preview'] ];
	}

}
