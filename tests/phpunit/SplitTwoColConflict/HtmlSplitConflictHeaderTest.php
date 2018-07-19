<?php

namespace TwoColConflict\Tests\SplitTwoColConflict;

use MediaWikiTestCase;
use TwoColConflict\SplitTwoColConflict\HtmlSplitConflictHeader;
use TwoColConflict\SplitTwoColConflict\SplitTwoColConflictHelper;
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
		$htmHeader = new HtmlSplitConflictHeader( $this->newTextConflictHelper() );
		$html = $htmHeader->getHtml();

		$this->assertTagExistsWithTextContents( $html, 'a', 'OtherUser' );
		$this->assertTagExistsWithTextContents( $html, 'p',
			'(twocolconflict-split-conflict-hint)' );
		$this->assertTagExistsWithTextContents( $html, 'span',
			'(twocolconflict-split-saved-at: 21 (july) 2018, 23:42)' );
	}

	/**
	 * @return SplitTwoColConflictHelper
	 */
	private function newTextConflictHelper() {
		$otherUser = User::newFromName( 'OtherUser' );

		$revision = $this->createMock( \Revision::class );
		$revision->method( 'getUser' )
			->willReturn( $otherUser->getId() );
		$revision->method( 'getUserText' )
			->willReturn( 'OtherUser' );
		$revision->method( 'getTimestamp' )
			->willReturn( '20180721234200' );

		$wikiPage = $this->createMock( \WikiPage::class );
		$wikiPage->method( 'getRevision' )
			->willReturn( $revision );

		$user = User::newFromName( 'TestUser' );

		$output = $this->createMock( \OutputPage::class );
		$output->method( 'getUser' )
			->willReturn( $user );
		$output->method( 'getContext' )
			->willReturn( \RequestContext::getMain() );

		$mock = $this->createMock( SplitTwoColConflictHelper::class );
		$mock->method( 'getWikiPage' )
			->willReturn( $wikiPage );
		$mock->method( 'getOutput' )
			->willReturn( $output );

		return $mock;
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
