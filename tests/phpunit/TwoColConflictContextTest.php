<?php

namespace TwoColConflict\Tests;

use ExtensionRegistry;
use Title;
use TwoColConflict\TwoColConflictContext;
use User;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\TwoColConflictContext
 * @license GPL-2.0-or-later
 */
class TwoColConflictContextTest extends \MediaWikiIntegrationTestCase {

	protected function setUp() : void {
		parent::setUp();

		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', false );
	}

	public function testIsUsedAsBetaFeature() {
		$this->assertFalse( TwoColConflictContext::isUsedAsBetaFeature() );

		if ( !ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' ) ) {
			$this->markTestSkipped( 'BetaFeatures not loaded' );
		}

		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', true );
		$this->assertTrue( TwoColConflictContext::isUsedAsBetaFeature() );
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
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' ) ) {
			$this->markTestSkipped( 'BetaFeatures not loaded' );
		}

		$this->setMwGlobals( [
			'wgTwoColConflictBetaFeature' => $betaConfig,
			'wgTwoColConflictSuggestResolution' => $singleColumnConfig,
		] );

		$result = TwoColConflictContext::shouldTwoColConflictBeShown( $user, $title );
		$this->assertSame( $expected, $result );
	}

	public function configurationProvider() {
		$defaultUser = $this->createUser();
		$betaUser = $this->createUser( '1', '1' );
		$optOutUser = $this->createUser( '0' );

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
				'expected' => false,
			],
			'user enabled Beta feature' => [
				'wgTwoColConflictBetaFeature' => true,
				'wgTwoColConflictSuggestResolution' => true,
				'user' => $betaUser,
				'title' => $defaultPage,
				'expected' => true,
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

	/**
	 * @dataProvider configurationProviderNoBetaFeatures
	 */
	public function testShouldTwoColConflictBeShown_noBetaFeatures(
		bool $betaConfig,
		bool $singleColumnConfig,
		User $user,
		Title $title,
		bool $expected
	) {
		if ( ExtensionRegistry::getInstance()->isLoaded( 'BetaFeatures' ) ) {
			$this->markTestSkipped( 'BetaFeatures not loaded' );
		}

		$this->setMwGlobals( [
			'wgTwoColConflictBetaFeature' => $betaConfig,
			'wgTwoColConflictSuggestResolution' => $singleColumnConfig,
		] );

		$result = TwoColConflictContext::shouldTwoColConflictBeShown( $user, $title );
		$this->assertSame( $expected, $result );
	}

	public function configurationProviderNoBetaFeatures() {
		$defaultUser = $this->createUser();
		$betaUser = $this->createUser( '1', '1' );

		$defaultPage = $this->createMock( Title::class );

		return [
			'enabled in beta mode when BetaFeatures not installed' => [
				'wgTwoColConflictBetaFeature' => true,
				'wgTwoColConflictSuggestResolution' => true,
				'user' => $defaultUser,
				'title' => $defaultPage,
				'expected' => true,
			],
			'enabled without BetaFeatures, also for an opted-in user' => [
				'wgTwoColConflictBetaFeature' => true,
				'wgTwoColConflictSuggestResolution' => true,
				'user' => $betaUser,
				'title' => $defaultPage,
				'expected' => true,
			],
		];
	}

	/**
	 * @dataProvider provideHasUserEnabledFeature
	 */
	public function testHasUserEnabledFeature(
		$betaPreference,
		$editingPreference,
		bool $expectedResult
	) {
		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', false );

		$user = $this->createUser( $editingPreference, $betaPreference );
		$contextStatic = TestingAccessWrapper::newFromClass( TwoColConflictContext::class );

		$result = $contextStatic->hasUserEnabledFeature( $user );
		$this->assertSame( $expectedResult, $result );
	}

	public function provideHasUserEnabledFeature() {
		// Note that 'editing' => null is impossible from the point of view of this
		//  function, in other words null and true are indistinguishable because the
		//  default value has already been merged into the option.
		return [
			[
				'beta' => null,
				'editing' => '0',
				'expected' => false,
			],
			[
				'beta' => null,
				'editing' => '1',
				'expected' => true,
			],
			[
				'beta' => '0',
				'editing' => '0',
				'expected' => false,
			],
			[
				'beta' => '0',
				'editing' => '1',
				'expected' => true,
			],
			[
				'beta' => '1',
				'editing' => '0',
				'expected' => false,
			],
			[
				'beta' => '1',
				'editing' => '1',
				'expected' => true,
			],
		];
	}

	private function createUser( string $enabled = '1', ?string $beta = null ) {
		$user = $this->createMock( User::class );
		$user->method( 'getOption' )->willReturnMap( [
			[ TwoColConflictContext::BETA_PREFERENCE_NAME, null, false, $beta ],
			[ TwoColConflictContext::ENABLED_PREFERENCE, null, false, $enabled ],
		] );
		$user->method( 'getBoolOption' )->willReturnMap( [
			[ TwoColConflictContext::BETA_PREFERENCE_NAME, (bool)$beta ],
			[ TwoColConflictContext::ENABLED_PREFERENCE, (bool)$enabled ],
		] );
		return $user;
	}

	/**
	 * @dataProvider provideShouldCoreHintBeShown
	 */
	public function testShouldCoreHintBeShown(
		bool $isAnon,
		bool $usedAsBeta,
		bool $enabledOpt,
		bool $hideHintOpt,
		bool $expectedResult
	) {
		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', $usedAsBeta );

		$user = $this->createMock( User::class );
		$user->method( 'getBoolOption' )->willReturnMap( [
			[ TwoColConflictContext::ENABLED_PREFERENCE, $enabledOpt ],
			[ TwoColConflictContext::HIDE_CORE_HINT_PREFERENCE, $hideHintOpt ],
		] );
		$user->method( 'isAnon' )->willReturn( $isAnon );

		$result = TwoColConflictContext::shouldCoreHintBeShown( $user );
		$this->assertSame( $expectedResult, $result );
	}

	public function provideShouldCoreHintBeShown() {
		return [
			[
				'isAnon' => true,
				'usedAsBeta' => false,
				'enabledOpt' => false,
				'hideHintOpt' => false,
				'expected' => false,
			],
			[
				'isAnon' => false,
				'usedAsBeta' => true,
				'enabledOpt' => false,
				'hideHintOpt' => false,
				'expected' => false,
			],
			[
				'isAnon' => false,
				'usedAsBeta' => false,
				'enabledOpt' => true,
				'hideHintOpt' => false,
				'expected' => false,
			],
			[
				'isAnon' => false,
				'usedAsBeta' => false,
				'enabledOpt' => false,
				'hideHintOpt' => true,
				'expected' => false,
			],
			[
				'isAnon' => false,
				'usedAsBeta' => false,
				'enabledOpt' => false,
				'hideHintOpt' => false,
				'expected' => true,
			],
		];
	}

}
