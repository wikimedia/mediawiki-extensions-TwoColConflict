<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use Language;
use MediaWiki\Revision\RevisionRecord;
use MediaWikiTestCase;
use TwoColConflict\SplitTwoColConflict\HtmlSplitConflictHeader;
use User;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\HtmlSplitConflictHeader
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlSplitConflictHeaderTest extends MediaWikiTestCase {

	/**
	 * @var User
	 */
	private $otherUser;

	/**
	 * @var int
	 */
	private $now;

	public function setUp() {
		parent::setUp();

		$this->setUserLang( 'qqx' );
		$this->otherUser = $this->getTestUser()->getUser();
		$this->now = 1000000000;
	}

	public function testGetHtmlMoreThan59MinutesAgo() {
		$htmHeader = new HtmlSplitConflictHeader(
			$this->newRevisionRecord( '20180721234200' ),
			$this->getTestUser()->getUser(),
			Language::factory( 'qqx' ),
			$this->now
		);
		$html = $htmHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'a', $this->otherUser->getName() );
		$this->assertTagExistsWithTextContents( $html, 'p',
			'(twocolconflict-split-conflict-hint)' );
		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-current-version-header: 23:42, 21 (july) 2018)' );
	}

	public function testGetHtml2MinutesAgo() {
		$ninetySecondsAgo = $this->now - 90;
		$htmlHeader = new HtmlSplitConflictHeader(
			$this->newRevisionRecord( $ninetySecondsAgo ),
			User::newFromName( 'TestUser' ),
			Language::factory( 'qqx' ),
			$this->now
		);
		$html = $htmlHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-current-version-header: (minutes-ago: 2))' );
	}

	public function testGetHtml2SecondsAgo() {
		$twoSecondsAgo = $this->now - 2;
		$htmlHeader = new HtmlSplitConflictHeader(
			$this->newRevisionRecord( $twoSecondsAgo ),
			User::newFromName( 'TestUser' ),
			Language::factory( 'qqx' ),
			$this->now
		);
		$html = $htmlHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-current-version-header: (seconds-ago: 2))' );
	}

	/**
	 * @param int|string $timestamp
	 *
	 * @return RevisionRecord
	 */
	private function newRevisionRecord( $timestamp ) {
		$revision = $this->createMock( RevisionRecord::class );
		$revision->method( 'getUser' )
			->willReturn( $this->otherUser );
		$revision->method( 'getTimestamp' )
			->willReturn( $timestamp );
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
