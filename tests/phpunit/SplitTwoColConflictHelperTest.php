<?php

namespace TwoColConflict\Tests;

use IBufferingStatsdDataFactory;
use MediaWiki\Content\IContentHandlerFactory;
use OutputPage;
use Title;
use TwoColConflict\SplitTwoColConflict\ResolutionSuggester;
use TwoColConflict\SplitTwoColConflict\SplitTwoColConflictHelper;

/**
 * @covers \TwoColConflict\SplitTwoColConflict\SplitTwoColConflictHelper
 */
class SplitTwoColConflictHelperTest extends \MediaWikiIntegrationTestCase {

	public function testBasics() {
		$title = $this->createMock( Title::class );
		$title->method( 'getContentModel' )->willReturn( '' );

		$out = $this->createMock( OutputPage::class );
		$out->expects( $this->never() )->method( 'addHTML' );

		$helper = new SplitTwoColConflictHelper(
			$title,
			$out,
			$this->createMock( IBufferingStatsdDataFactory::class ),
			'',
			'',
			$this->createMock( IContentHandlerFactory::class ),
			$this->createMock( ResolutionSuggester::class )
		);

		$this->assertSame( '', $helper->getExplainHeader() );
		$this->assertSame( '', $helper->getEditConflictMainTextBox() );
		// This should not trigger OutputPage::addHTML(), asserted above
		$helper->showEditFormTextAfterFooters();
	}

}
