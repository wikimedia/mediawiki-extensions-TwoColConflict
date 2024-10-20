<?php

namespace TwoColConflict\Tests;

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Language\Language;
use MediaWiki\MainConfigNames;
use MediaWiki\Message\Message;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\PageIdentityValue;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\MutableRevisionRecord;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWikiIntegrationTestCase;
use MessageLocalizer;
use TwoColConflict\Html\HtmlSplitConflictHeader;

/**
 * @covers \TwoColConflict\Html\HtmlSplitConflictHeader
 *
 * @group Database
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlSplitConflictHeaderTest extends MediaWikiIntegrationTestCase {

	private const NOW = 1000000000;

	protected function setUp(): void {
		parent::setUp();

		// intentionally not reset in teardown, see Icb6901f4d5
		OutputPage::setupOOUI();

		$this->overrideConfigValue( MainConfigNames::LanguageCode, 'qqx' );
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
		$this->assertStringContainsString( '>(twocolconflict-copy-tab-action)<', $html );
		$this->assertStringContainsString( 'Special:ProvideSubmittedText/Talk:Original_page', $html );
	}

	public function testGetHtmlMoreThan23HoursAgo() {
		$htmlHeader = $this->newConflictHeader(
			'',
			$this->newRevisionRecord( '20180721234200' )
		);
		$html = $htmlHeader->getHtml();

		$this->assertStringContainsString( '>' . __CLASS__ . '<', $html );
		$this->assertStringContainsString( '>(twocolconflict-split-header-hint)<', $html );
		$this->assertStringContainsString(
			'>(twocolconflict-split-current-version-header: 20180721234200)<',
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

	private function newConflictHeader( string $summary, RevisionRecord $revision = null ) {
		$user = $this->createNoOpMock( Authority::class, [ 'getUser' ] );

		$language = $this->createMock( Language::class );
		$language->method( 'userTimeAndDate' )->willReturnCallback( static function ( ?string $ts ) {
			return $ts ?? '(just-now)';
		} );

		$localizer = $this->createMock( MessageLocalizer::class );
		$localizer->method( 'msg' )->willReturnCallback( function ( $key, ...$params ) {
			$msg = $this->createMock( Message::class );
			$text = "($key" . ( $params ? ': ' . implode( '|', $params ) : '' ) . ')';
			$msg->method( $this->logicalOr( 'escaped', 'parse', 'text' ) )->willReturn( $text );
			$msg->method( 'rawParams' )->willReturnCallback( function ( ...$params ) use ( $key ) {
				// fallback for the copy links
				if ( str_contains( $params[0], 'twocolconflict-copy-tab-action' ) ) {
					return $params[0];
				}
				$msg = $this->createMock( Message::class );
				$msg->method( 'escaped' )->willReturn( "($key: " . implode( '|', $params ) . ')' );
				return $msg;
			} );
			return $msg;
		} );

		return new HtmlSplitConflictHeader(
			Title::makeTitle( NS_TALK, 'Original page' ),
			$user,
			$summary,
			$language,
			$localizer,
			$this->getServiceContainer()->getCommentFormatter(),
			self::NOW,
			$revision
		);
	}

	/**
	 * @param string $timestamp
	 * @param string $editSummary
	 *
	 * @return RevisionRecord
	 */
	private function newRevisionRecord( string $timestamp, string $editSummary = '' ): MutableRevisionRecord {
		$user = $this->createMock( User::class );
		$user->method( 'getName' )->willReturn( __CLASS__ );

		$revision = new MutableRevisionRecord(
			new PageIdentityValue( 0, NS_MAIN, __CLASS__, PageIdentityValue::LOCAL )
		);
		$revision->setUser( $user )
			->setTimestamp( $timestamp )
			->setComment( CommentStoreComment::newUnsavedComment( $editSummary ) );
		return $revision;
	}

}
