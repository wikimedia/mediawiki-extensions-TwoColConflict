<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Linker;
use Message;

class HtmlSplitConflictHeader {

	/**
	 * @var SplitTwoColConflictHelper
	 */
	private $conflictHelper;

	/**
	 * @param SplitTwoColConflictHelper $conflictHelper
	 */
	public function __construct( SplitTwoColConflictHelper $conflictHelper ) {
		$this->conflictHelper = $conflictHelper;
	}

	/**
	 * @return string
	 */
	public function getHtml() {
		$out = $this->getWarningMessage(
			wfMessage( 'twocolconflict-split-conflict-hint' )->parse()
		);

		$out .= Html::openElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-header' ]
		);

		$out .= Html::openElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-flex-header' ]
		);

		$out .= $this->buildCurrentVersionHeader();
		$out .= $this->buildYourVersionHeader();

		$out .= Html::closeElement( 'div' );
		$out .= Html::closeElement( 'div' );

		return $out;
	}

	private function buildCurrentVersionHeader() {
		return $this->buildVersionHeader(
			wfMessage(
				'twocolconflict-split-current-version-header',
				$this->getLastRevUserLink()
			),
			wfMessage( 'twocolconflict-split-saved-at' ),
			'mw-twocolconflict-split-current-version-header',
			$this->conflictHelper->getWikiPage()->getRevision()->getTimestamp()
		);
	}

	private function buildYourVersionHeader() {
		return $this->buildVersionHeader(
			wfMessage( 'twocolconflict-split-your-version-header' ),
			wfMessage( 'twocolconflict-split-not-saved-at' ),
			'mw-twocolconflict-split-your-version-header',
			new \MWTimestamp()
		);
	}

	private function buildVersionHeader(
		Message $headerMsg,
		Message $dateMsg,
		$class,
		$timestamp
	) {
		return Html::openElement( 'div', [ 'class' => $class ] ) .
			Html::rawElement( 'span', [], $headerMsg->plain() ) .
			Html::Element( 'br' ) .
			Html::rawElement( 'span', [], $this->getFormattedDateTime( $dateMsg, $timestamp ) ) .
			Html::closeElement( 'div' );
	}

	private function getLastRevUserLink() {
		$currentRev = $this->conflictHelper->getWikiPage()->getRevision();
		return Linker::userLink( $currentRev->getUser(), $currentRev->getUserText() );
	}

	private function getFormattedDateTime( Message $dateMsg, $timestamp ) {
		$language = $this->conflictHelper->getOutput()->getContext()->getLanguage();
		$user = $this->conflictHelper->getOutput()->getUser();

		$d = $language->userDate( $timestamp, $user );
		$t = $language->userTime( $timestamp, $user );

		$dateMsg->params( $d, $t );

		return $dateMsg->parse();
	}

	private function getWarningMessage( $message ) {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-warningbox' ],
			Html::rawElement( 'p', [], $message )
		);
	}

}
