<?php

namespace TwoColConflict\Tests;

use Title;
use TwoColConflict\TwoColConflictContext;
use User;

/**
 * @covers \TwoColConflict\TwoColConflictContext
 * @license GPL-2.0-or-later
 */
class TwoColConflictContextTest extends \MediaWikiIntegrationTestCase {

	public function testIsUsedAsBetaFeature() {
		$this->assertTrue( TwoColConflictContext::isUsedAsBetaFeature(), 'default' );

		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', false );
		$this->assertFalse( TwoColConflictContext::isUsedAsBetaFeature() );
	}

	/**
	 * @dataProvider configurationProvider
	 */
	public function testShouldTwoColConflictBeShown(
		bool $betaConfig,
		bool $singleColumnConfig,
		User $user,
		Title $title,
		bool $expected
	) {
		$this->setMwGlobals( [
			'wgTwoColConflictBetaFeature' => $betaConfig,
			'wgTwoColConflictSuggestResolution' => $singleColumnConfig,
		] );
		$result = TwoColConflictContext::shouldTwoColConflictBeShown( $user, $title );
		$this->assertSame( $expected, $result );
	}

	public function configurationProvider() {
		$defaultUser = $this->createUser();
		$optOutUser = $this->createUser( false );

		$betaPossible = \ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' );
		$betaUser = $this->createUser( false );
		$betaUser->method( 'getOption' )
			->with( 'twocolconflict' )
			->willReturn( '1' );

		$defaultPage = $this->createMock( Title::class );

		$talkPage = $this->createMock( Title::class );
		$talkPage->method( 'isTalkPage' )
			->willReturn( true );

		$projectPage = $this->createMock( Title::class );
		$projectPage->method( 'inNamespace' )
			->with( NS_PROJECT )
			->willReturn( true );

		return [
			'disabled in Beta' => [
				'wgTwoColConflictBetaFeature' => true,
				'wgTwoColConflictSuggestResolution' => true,
				'user' => $defaultUser,
				'title' => $defaultPage,
				'expected' => !$betaPossible,
			],
			'user enabled Beta feature' => [
				'wgTwoColConflictBetaFeature' => true,
				'wgTwoColConflictSuggestResolution' => true,
				'user' => $betaUser,
				'title' => $defaultPage,
				'expected' => $betaPossible,
			],
			'enabled by default when not in Beta any more' => [
				'wgTwoColConflictBetaFeature' => false,
				'wgTwoColConflictSuggestResolution' => true,
				'user' => $defaultUser,
				'title' => $defaultPage,
				'expected' => true,
			],
			'user disabled new interface' => [
				'wgTwoColConflictBetaFeature' => false,
				'wgTwoColConflictSuggestResolution' => true,
				'user' => $optOutUser,
				'title' => $defaultPage,
				'expected' => false,
			],
			'disabled on talk pages' => [
				'wgTwoColConflictBetaFeature' => false,
				'wgTwoColConflictSuggestResolution' => false,
				'user' => $defaultUser,
				'title' => $talkPage,
				'expected' => false,
			],
			'disabled in the project namespace' => [
				'wgTwoColConflictBetaFeature' => false,
				'wgTwoColConflictSuggestResolution' => false,
				'user' => $defaultUser,
				'title' => $projectPage,
				'expected' => false,
			],
		];
	}

	private function createUser( bool $enabled = true ) {
		$user = $this->createMock( User::class );
		$user->method( 'getBoolOption' )
			->with( 'twocolconflict-enabled' )
			->willReturn( $enabled );
		return $user;
	}

}
