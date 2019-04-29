<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use Language;
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

	const NOW = 1000000000;

	/**
	 * @var User
	 */
	private $otherUser;

	public function setUp() {
		parent::setUp();

		$this->setUserLang( 'qqx' );
		$this->otherUser = $this->getTestUser()->getUser();
	}

	public function testConflictOnNewPage() {
		$htmlHeader = new HtmlSplitConflictHeader(
			Title::newFromText( __METHOD__ ),
			$this->getTestUser()->getUser(),
			Language::factory( 'qqx' ),
			self::NOW,
			''
		);
		$html = $htmlHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-current-version-header: (just-now))' );
		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-saved-at: )' );
		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-your-version-header)' );
		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-not-saved-at)' );
	}

	public function testGetHtmlMoreThan23HoursAgo() {
		$htmlHeader = new HtmlSplitConflictHeader(
			Title::newFromText( __METHOD__ ),
			$this->getTestUser()->getUser(),
			Language::factory( 'qqx' ),
			self::NOW,
			'',
			$this->newRevisionRecord( '20180721234200' )
		);
		$html = $htmlHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'a', $this->otherUser->getName() );
		$this->assertTagExistsWithTextContents( $html, 'p',
			'(twocolconflict-split-conflict-hint)' );
		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-current-version-header: 23:42, 21 (july) 2018)' );
	}

	public function testGetHtml2HoursAgo() {
		$ninetyMinutesAgo = self::NOW - 90 * 60;
		$htmlHeader = new HtmlSplitConflictHeader(
			Title::newFromText( __METHOD__ ),
			$this->getTestUser()->getUser(),
			Language::factory( 'qqx' ),
			self::NOW,
			'',
			$this->newRevisionRecord( $ninetyMinutesAgo )
		);
		$html = $htmlHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-current-version-header: (hours-ago: 2))' );
	}

	public function testGetHtml2MinutesAgo() {
		$ninetySecondsAgo = self::NOW - 90;
		$htmlHeader = new HtmlSplitConflictHeader(
			Title::newFromText( __METHOD__ ),
			$this->getTestUser()->getUser(),
			Language::factory( 'qqx' ),
			self::NOW,
			'',
			$this->newRevisionRecord( $ninetySecondsAgo )
		);
		$html = $htmlHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-current-version-header: (minutes-ago: 2))' );
	}

	public function testGetHtml2SecondsAgo() {
		$twoSecondsAgo = self::NOW - 2;
		$htmlHeader = new HtmlSplitConflictHeader(
			Title::newFromText( __METHOD__ ),
			$this->getTestUser()->getUser(),
			Language::factory( 'qqx' ),
			self::NOW,
			'',
			$this->newRevisionRecord( $twoSecondsAgo )
		);
		$html = $htmlHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-current-version-header: (seconds-ago: 2))' );
	}

	public function testGetHtmlWithEditSummaries() {
		$htmlHeader = new HtmlSplitConflictHeader(
			Title::newFromText( __METHOD__ ),
			$this->getTestUser()->getUser(),
			Language::factory( 'qqx' ),
			self::NOW,
			'Conflicting edit summary',
			$this->newRevisionRecord( self::NOW, 'Latest revision summary' )
		);
		$html = $htmlHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'span',
			'(parentheses: Latest revision summary)' );
		$this->assertTagExistsWithTextContents( $html, 'span',
			'(parentheses: Conflicting edit summary)' );
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

	private function assertTagExistsWithTextContents( $html, $tagName, $value ) {
		assertThat(
			$html,
			is( htmlPiece( havingChild( both(
				withTagName( $tagName ) )
				->andAlso( havingTextContents( $value ) )
			) ) )
		);
		$this->addToAssertionCount( 1 );
	}

}
