<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Language;
use Linker;
use MediaWiki\Revision\RevisionRecord;
use Message;
use User;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class HtmlSplitConflictHeader {

	/**
	 * @var RevisionRecord
	 */
	private $revision;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var Language
	 */
	private $language;

	public function __construct( RevisionRecord $revision, User $user, Language $language ) {
		$this->revision = $revision;
		$this->user = $user;
		$this->language = $language;
	}

	/**
	 * @return string HTML
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
			$this->revision->getTimestamp()
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

	/**
	 * @param Message $headerMsg
	 * @param Message $dateMsg
	 * @param string $class
	 * @param string|\MWTimestamp $timestamp
	 *
	 * @return string HTML
	 */
	private function buildVersionHeader(
		Message $headerMsg,
		Message $dateMsg,
		$class,
		$timestamp
	) {
		return Html::openElement( 'div', [ 'class' => $class ] ) .
			Html::rawElement( 'span', [], $headerMsg->plain() ) .
			Html::element( 'br' ) .
			Html::rawElement( 'span', [], $this->getFormattedDateTime( $dateMsg, $timestamp ) ) .
			Html::closeElement( 'div' );
	}

	/**
	 * @return string HTML
	 */
	private function getLastRevUserLink() {
		$user = $this->revision->getUser();
		return Linker::userLink( $user->getId(), $user->getName() );
	}

	/**
	 * @param Message $dateMsg
	 * @param string|\MWTimestamp $timestamp
	 *
	 * @return string HTML
	 */
	private function getFormattedDateTime( Message $dateMsg, $timestamp ) {
		$t = $this->language->userTimeAndDate( $timestamp, $this->user );

		$dateMsg->params( $t );

		return $dateMsg->parse();
	}

	/**
	 * @param string $message
	 *
	 * @return string HTML
	 */
	private function getWarningMessage( $message ) {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-warningbox' ],
			Html::rawElement( 'p', [], $message )
		);
	}

}
