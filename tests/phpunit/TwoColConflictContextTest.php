<?php

namespace TwoColConflict\Tests;

use ExtensionRegistry;
use HashConfig;
use Title;
use TwoColConflict\TwoColConflictContext;
use User;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\TwoColConflictContext
 * @license GPL-2.0-or-later
 */
class TwoColConflictContextTest extends \MediaWikiIntegrationTestCase {

	public function testIsUsedAsBetaFeature() {
		$registry = $this->createExtensionRegistry();
		$twoColContext = new TwoColConflictContext( $this->createConfig(), $registry );
		$this->assertFalse( $twoColContext->isUsedAsBetaFeature() );

		$twoColContext = new TwoColConflictContext( $this->createConfig( true ), $registry );
		$this->assertTrue( $twoColContext->isUsedAsBetaFeature() );
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
		$twoColContext = new TwoColConflictContext(
			$this->createConfig( $betaConfig, $singleColumnConfig ),
			$this->createExtensionRegistry()
		);
		$result = $twoColContext->shouldTwoColConflictBeShown( $user, $title );
		$this->assertSame( $expected, $result );
	}

	public function configurationProvider() {
		$defaultUser = $this->createUser();
		$betaUser = $this->createUser( '1', '1' );
		$optOutUser = $this->createUser( '0' );

		$defaultPage = Title::makeTitle( NS_MAIN, __CLASS__ );
		$talkPage = Title::makeTitle( NS_TALK, __CLASS__ );
		$projectPage = Title::makeTitle( NS_PROJECT, __CLASS__ );

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
		$twoColContext = new TwoColConflictContext(
			$this->createConfig( $betaConfig, $singleColumnConfig ),
			$this->createExtensionRegistry( false )
		);
		$result = $twoColContext->shouldTwoColConflictBeShown( $user, $title );
		$this->assertSame( $expected, $result );
	}

	public function configurationProviderNoBetaFeatures() {
		$defaultUser = $this->createUser();
		$betaUser = $this->createUser( '1', '1' );

		$defaultPage = Title::makeTitle( NS_MAIN, __CLASS__ );

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
		$user = $this->createUser( $editingPreference, $betaPreference );
		/** @var TwoColConflictContext $twoColContext */
		$twoColContext = TestingAccessWrapper::newFromObject( new TwoColConflictContext(
			$this->createConfig( false ),
			$this->createExtensionRegistry()
		) );

		$result = $twoColContext->hasUserEnabledFeature( $user );
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
		bool $isRegistered,
		bool $usedAsBeta,
		bool $enabledOpt,
		bool $hideHintOpt,
		bool $expectedResult
	) {
		$user = $this->createMock( User::class );
		$user->method( 'getBoolOption' )->willReturnMap( [
			[ TwoColConflictContext::ENABLED_PREFERENCE, $enabledOpt ],
			[ TwoColConflictContext::HIDE_CORE_HINT_PREFERENCE, $hideHintOpt ],
		] );
		$user->method( 'isRegistered' )->willReturn( $isRegistered );

		$twoColContext = new TwoColConflictContext(
			$this->createConfig( $usedAsBeta ),
			$this->createExtensionRegistry()
		);
		$result = $twoColContext->shouldCoreHintBeShown( $user );
		$this->assertSame( $expectedResult, $result );
	}

	public function provideShouldCoreHintBeShown() {
		return [
			[
				'isRegistered' => false,
				'usedAsBeta' => false,
				'enabledOpt' => false,
				'hideHintOpt' => false,
				'expected' => false,
			],
			[
				'isRegistered' => true,
				'usedAsBeta' => true,
				'enabledOpt' => false,
				'hideHintOpt' => false,
				'expected' => false,
			],
			[
				'isRegistered' => true,
				'usedAsBeta' => false,
				'enabledOpt' => true,
				'hideHintOpt' => false,
				'expected' => false,
			],
			[
				'isRegistered' => true,
				'usedAsBeta' => false,
				'enabledOpt' => false,
				'hideHintOpt' => true,
				'expected' => false,
			],
			[
				'isRegistered' => true,
				'usedAsBeta' => false,
				'enabledOpt' => false,
				'hideHintOpt' => false,
				'expected' => true,
			],
		];
	}

	private function createConfig( bool $isBetaFeature = false, bool $suggestResolution = true ) {
		return new HashConfig( [
			'TwoColConflictBetaFeature' => $isBetaFeature,
			'TwoColConflictSuggestResolution' => $suggestResolution,
		] );
	}

	private function createExtensionRegistry( bool $isLoaded = true ) {
		$registry = $this->createMock( ExtensionRegistry::class );
		$registry->method( 'isLoaded' )->willReturn( $isLoaded );
		return $registry;
	}

}
