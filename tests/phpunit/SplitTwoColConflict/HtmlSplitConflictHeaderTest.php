<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWikiTestCase;
use Title;
use TwoColConflict\SplitTwoColConflict\HtmlSplitConflictHeader;
use User;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\HtmlSplitConflictHeader
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlSplitConflictHeaderTest extends MediaWikiTestCase {

	private const NOW = 1000000000;

	/**
	 * @var User
	 */
	private $otherUser;

	public function setUp() : void {
		parent::setUp();

		$this->setUserLang( 'qqx' );
		$this->otherUser = $this->getTestUser()->getUser();
	}

	public function testConflictOnNewPage() {
		$htmlHeader = $this->newConflictHeader(
			''
		);
		$html = $htmlHeader->getHtml();

		$this->assertStringContainsString(
			'>(twocolconflict-split-current-version-header: (just-now))<',
			$html
		);
		$this->assertStringContainsString( '>(twocolconflict-split-saved-at: )<', $html );
		$this->assertStringContainsString( '>(twocolconflict-split-your-version-header)<', $html );
		$this->assertStringContainsString( '>(twocolconflict-split-not-saved-at)<', $html );
	}

	public function testGetHtmlMoreThan23HoursAgo() {
		$htmlHeader = $this->newConflictHeader(
			'',
			$this->newRevisionRecord( '20180721234200' )
		);
		$html = $htmlHeader->getHtml();

		$this->assertStringContainsString( '>' . $this->otherUser->getName() . '<', $html
		);
		$this->assertStringContainsString( '>(twocolconflict-split-header-hint-beta)<', $html );
		$this->assertStringContainsString(
			'>(twocolconflict-split-current-version-header: 23:42, 21 (july) 2018)<',
			$html
		);
	}

	public function testGetHtml2HoursAgo() {
		$ninetyMinutesAgo = self::NOW - 90 * 60;
		$htmlHeader = $this->newConflictHeader(
			'',
			$this->newRevisionRecord( $ninetyMinutesAgo )
		);
		$html = $htmlHeader->getHtml();

		$this->assertStringContainsString(
			'>(twocolconflict-split-current-version-header: (hours-ago: 2))<',
			$html
		);
	}

	public function testGetHtml2MinutesAgo() {
		$ninetySecondsAgo = self::NOW - 90;
		$htmlHeader = $this->newConflictHeader(
			'',
			$this->newRevisionRecord( $ninetySecondsAgo )
		);
		$html = $htmlHeader->getHtml();

		$this->assertStringContainsString(
			'>(twocolconflict-split-current-version-header: (minutes-ago: 2))<',
			$html
		);
	}

	public function testGetHtml2SecondsAgo() {
		$twoSecondsAgo = self::NOW - 2;
		$htmlHeader = $this->newConflictHeader(
			'',
			$this->newRevisionRecord( $twoSecondsAgo )
		);
		$html = $htmlHeader->getHtml();

		$this->assertStringContainsString(
			'>(twocolconflict-split-current-version-header: (seconds-ago: 2))<',
			$html
		);
	}

	public function testGetHtmlWithEditSummaries() {
		$htmlHeader = $this->newConflictHeader(
			'Conflicting edit summary',
			$this->newRevisionRecord( self::NOW, 'Latest revision summary' )
		);
		$html = $htmlHeader->getHtml();

		$this->assertStringContainsString( '>(parentheses: Latest revision summary)<', $html );
		$this->assertStringContainsString( '>(parentheses: Conflicting edit summary)<', $html );
	}

	private function newConflictHeader( $summary, $revision = null ) {
		return new HtmlSplitConflictHeader(
			Title::newFromText( __METHOD__ ),
			$this->getTestUser()->getUser(),
			MediaWikiServices::getInstance()->getLanguageFactory()->getLanguage( 'qqx' ),
			self::NOW,
			$summary,
			$revision
		);
	}

	/**
	 * @param int|string $timestamp
	 * @param string $editSummary
	 *
	 * @return RevisionRecord
	 */
	private function newRevisionRecord( $timestamp, $editSummary = '' ) {
		$revision = $this->createMock( RevisionRecord::class );
		$revision->method( 'getUser' )
			->willReturn( $this->otherUser );
		$revision->method( 'getTimestamp' )
			->willReturn( $timestamp );
		$revision->method( 'getComment' )
			->willReturn( $editSummary ? (object)[ 'text' => $editSummary ] : null );
		return $revision;
	}

}
