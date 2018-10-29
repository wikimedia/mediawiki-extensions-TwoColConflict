<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Language;
use Linker;
use MediaWiki\Revision\RevisionRecord;
use Message;
use User;
use Wikimedia\Timestamp\ConvertibleTimestamp;

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

	/**
	 * @var ConvertibleTimestamp
	 */
	private $now;

	/**
	 * @param RevisionRecord $revision
	 * @param User $user
	 * @param Language $language
	 * @param string|int|false $now Any value the ConvertibleTimestamp class accepts. False for the
	 *  current time
	 */
	public function __construct(
		RevisionRecord $revision,
		User $user,
		Language $language,
		$now
	) {
		$this->revision = $revision;
		$this->user = $user;
		$this->language = $language;
		$this->now = new ConvertibleTimestamp( $now );
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
				$this->getFormattedDateTime()
			),
			wfMessage( 'twocolconflict-split-saved-at' )
				->rawParams( $this->getLastRevUserLink() ),
			'mw-twocolconflict-split-current-version-header'
		);
	}

	private function buildYourVersionHeader() {
		return $this->buildVersionHeader(
			wfMessage( 'twocolconflict-split-your-version-header' ),
			wfMessage( 'twocolconflict-split-not-saved-at' ),
			'mw-twocolconflict-split-your-version-header'
		);
	}

	/**
	 * @param Message $dateMsg
	 * @param Message $userMsg
	 * @param string $class
	 *
	 * @return string HTML
	 */
	private function buildVersionHeader(
		Message $dateMsg,
		Message $userMsg,
		$class
	) {
		return Html::openElement( 'div', [ 'class' => $class ] ) .
			Html::element( 'span', [], $dateMsg->text() ) .
			Html::element( 'br' ) .
			Html::rawElement( 'span', [], $userMsg->escaped() ) .
			Html::closeElement( 'div' );
	}

	/**
	 * @return string HTML
	 */
	private function getLastRevUserLink() {
		/** @suppress PhanDeprecatedClass Linker::revUserTools shouldn't need a Revision, but does */
		return Linker::revUserTools( new \Revision( $this->revision ) );
	}

	/**
	 * @return string
	 */
	private function getFormattedDateTime() {
		$timestamp = $this->revision->getTimestamp();
		$diff = ( new ConvertibleTimestamp( $timestamp ) )->diff( $this->now );

		if ( $diff->days || $diff->h ) {
			return $this->language->userTimeAndDate( $timestamp, $this->user );
		}

		if ( $diff->i ) {
			return wfMessage( 'minutes-ago', round( $diff->i + $diff->s / 60 ) )->text();
		}

		if ( $diff->s ) {
			return wfMessage( 'seconds-ago', $diff->s )->text();
		}

		return wfMessage( 'just-now' )->text();
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
