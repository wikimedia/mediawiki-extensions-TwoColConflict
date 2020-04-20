<?php

namespace TwoColConflict\SplitTwoColConflict;

use Html;
use Language;
use Linker;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Revision\RevisionRecord;
use Message;
use TwoColConflict\TwoColConflictContext;
use User;
use Wikimedia\Timestamp\ConvertibleTimestamp;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class HtmlSplitConflictHeader {

	/**
	 * @var LinkTarget
	 */
	private $linkTarget;

	/**
	 * @var RevisionRecord|null
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
	 * @var string
	 */
	private $newEditSummary;

	/**
	 * @param LinkTarget $linkTarget
	 * @param User $user
	 * @param Language $language
	 * @param string|int|false $now Any value the ConvertibleTimestamp class accepts. False for the
	 *  current time
	 * @param string $newEditSummary
	 * @param RevisionRecord|null $revision Latest revision for testing, derived from the
	 *  $linkTarget otherwise.
	 */
	public function __construct(
		LinkTarget $linkTarget,
		User $user,
		Language $language,
		$now,
		string $newEditSummary,
		RevisionRecord $revision = null
	) {
		$this->linkTarget = $linkTarget;
		$this->revision = $revision ?? $this->getLatestRevision();
		$this->user = $user;
		$this->language = $language;
		$this->now = new ConvertibleTimestamp( $now );
		$this->newEditSummary = $newEditSummary;
	}

	/**
	 * @return RevisionRecord|null
	 */
	private function getLatestRevision() : ?RevisionRecord {
		$wikiPage = \WikiPage::factory( \Title::newFromLinkTarget( $this->linkTarget ) );
		/** @see https://phabricator.wikimedia.org/T203085 */
		$wikiPage->loadPageData( \WikiPage::READ_LATEST );
		return $wikiPage->getRevisionRecord();
	}

	/**
	 * @return string HTML
	 */
	public function getHtml() : string {
		$hintMsg = TwoColConflictContext::isUsedAsBetaFeature() ?
			'twocolconflict-split-header-hint-beta' : 'twocolconflict-split-header-hint';

		$out = $this->getWarningMessage( $hintMsg );
		$out .= Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-header' ],
			Html::rawElement(
				'div',
				[ 'class' => 'mw-twocolconflict-split-flex-header' ],
				$this->buildCurrentVersionHeader() .
					$this->buildYourVersionHeader()
			)
		);
		return $out;
	}

	private function buildCurrentVersionHeader() : string {
		$dateTime = wfMessage( 'just-now' )->text();
		$userTools = '';
		$summary = '';

		if ( $this->revision ) {
			$dateTime = $this->getFormattedDateTime( $this->revision->getTimestamp() );
			$userTools = Linker::revUserTools( $this->revision );

			$comment = $this->revision->getComment( RevisionRecord::FOR_THIS_USER, $this->user );
			if ( $comment ) {
				$summary = $comment->text;
			}
		}

		return $this->buildVersionHeader(
			wfMessage( 'twocolconflict-split-current-version-header', $dateTime ),
			wfMessage( 'twocolconflict-split-saved-at' )->rawParams( $userTools ),
			$summary,
			'mw-twocolconflict-split-current-version-header'
		);
	}

	private function buildYourVersionHeader() : string {
		return $this->buildVersionHeader(
			wfMessage( 'twocolconflict-split-your-version-header' ),
			wfMessage( 'twocolconflict-split-not-saved-at' ),
			$this->newEditSummary,
			'mw-twocolconflict-split-your-version-header'
		);
	}

	/**
	 * @param Message $dateMsg
	 * @param Message $userMsg
	 * @param string $summary
	 * @param string $class
	 *
	 * @return string HTML
	 */
	private function buildVersionHeader(
		Message $dateMsg,
		Message $userMsg,
		string $summary,
		string $class
	) : string {
		$html = Html::element( 'span', [], $dateMsg->text() ) .
			Html::element( 'br' ) .
			Html::rawElement( 'span', [], $userMsg->escaped() );

		if ( $summary !== '' ) {
			$summaryMsg = wfMessage( 'parentheses' )
				->rawParams( Linker::formatComment( $summary, $this->linkTarget ) );
			$html .= Html::element( 'br' ) .
				Html::rawElement( 'span', [ 'class' => 'comment' ], $summaryMsg->escaped() );
		}

		return Html::rawElement( 'div', [ 'class' => $class ], $html );
	}

	/**
	 * @param string|null $timestamp
	 *
	 * @return string
	 */
	private function getFormattedDateTime( ?string $timestamp ) : string {
		$diff = ( new ConvertibleTimestamp( $timestamp ?: false ) )->diff( $this->now );

		if ( $diff->days ) {
			return $this->language->userTimeAndDate( $timestamp, $this->user );
		}

		if ( $diff->h ) {
			$minutes = $diff->i + $diff->s / 60;
			return wfMessage( 'hours-ago', round( $diff->h + $minutes / 60 ) )->text();
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
	 * @param string $messageKey
	 *
	 * @return string HTML
	 */
	private function getWarningMessage( string $messageKey ) : string {
		$html = wfMessage( $messageKey )->parse();
		// Force feedback links to be opened in a new tab, and not loose the edit
		$html = preg_replace( '/<a\b(?![^<>]*\starget=)/', '<a target="_blank"', $html );
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-warningbox' ],
			Html::rawElement( 'p', [], $html )
		);
	}

}
