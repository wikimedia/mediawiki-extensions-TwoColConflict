<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use Language;
use MediaWiki\Storage\RevisionRecord;
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

	public function setUp() {
		parent::setUp();

		$this->setUserLang( 'qqx' );
	}

	public function testGetHtml() {
		$htmHeader = new HtmlSplitConflictHeader(
			$this->newRevisionRecord(),
			User::newFromName( 'TestUser' ),
			Language::factory( 'qqx' )
		);
		$html = $htmHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'a', 'OtherUser' );
		$this->assertTagExistsWithTextContents( $html, 'p',
			'(twocolconflict-split-conflict-hint)' );
		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-saved-at: 21 (july) 2018, 23:42)' );
	}

	/**
	 * @return RevisionRecord
	 */
	private function newRevisionRecord() {
		$otherUser = User::newFromName( 'OtherUser' );
		$revision = $this->createMock( RevisionRecord::class );
		$revision->method( 'getUser' )
			->willReturn( $otherUser );
		$revision->method( 'getTimestamp' )
			->willReturn( '20180721234200' );
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
