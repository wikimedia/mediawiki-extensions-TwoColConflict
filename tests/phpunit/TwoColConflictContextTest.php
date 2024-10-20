<?php

namespace TwoColConflict\Tests;

use MediaWiki\Config\HashConfig;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Title\Title;
use MediaWiki\User\Options\StaticUserOptionsLookup;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserIdentityValue;
use TwoColConflict\TwoColConflictContext;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\TwoColConflictContext
 *
 * @license GPL-2.0-or-later
 */
class TwoColConflictContextTest extends \MediaWikiIntegrationTestCase {

	public function testIsUsedAsBetaFeature() {
		$registry = $this->createExtensionRegistry();
		$twoColContext = new TwoColConflictContext(
			$this->createConfig(),
			new StaticUserOptionsLookup( [] ),
			$registry
		);
		$this->assertFalse( $twoColContext->isUsedAsBetaFeature() );

		$twoColContext = new TwoColConflictContext(
			$this->createConfig( true ),
			new StaticUserOptionsLookup( [] ),
			$registry
		);
		$this->assertTrue( $twoColContext->isUsedAsBetaFeature() );
	}

	/**
	 * @dataProvider configurationProvider
	 */
	public function testShouldTwoColConflictBeShown(
		bool $betaConfig,
		bool $singleColumnConfig,
		UserOptionsLookup $userOptionsLookup,
		int $namespace,
		bool $expected
	) {
		if ( $betaConfig ) {
			$this->markTestSkippedIfExtensionNotLoaded( 'BetaFeatures' );
		}

		$user = $this->createMock( User::class );
		// Note: Only needed by BetaFeatures
		$this->setService( 'UserOptionsLookup', $userOptionsLookup );

		$title = $this->createTitle( $namespace );

		$twoColContext = new TwoColConflictContext(
			$this->createConfig( $betaConfig, $singleColumnConfig ),
			$userOptionsLookup,
			$this->createExtensionRegistry()
		);
		$result = $twoColContext->shouldTwoColConflictBeShown( $user, $title );
		$this->assertSame( $expected, $result );
	}

	public static function configurationProvider() {
		$defaultUser = self::createUserOptionsLookup();
		$betaUser = self::createUserOptionsLookup( '1', '1' );
		$optOutUser = self::createUserOptionsLookup( '0' );

		return [
			'disabled in Beta' => [
				'wgTwoColConflictBetaFeature' => true,
				'wgTwoColConflictSuggestResolution' => true,
				'userOptionsLookup' => $defaultUser,
				'namespace' => NS_MAIN,
				'expected' => false,
			],
			'user enabled Beta feature' => [
				'wgTwoColConflictBetaFeature' => true,
				'wgTwoColConflictSuggestResolution' => true,
				'userOptionsLookup' => $betaUser,
				'namespace' => NS_MAIN,
				'expected' => true,
			],
			'enabled by default when not in Beta any more' => [
				'wgTwoColConflictBetaFeature' => false,
				'wgTwoColConflictSuggestResolution' => true,
				'userOptionsLookup' => $defaultUser,
				'namespace' => NS_MAIN,
				'expected' => true,
			],
			'user disabled new interface' => [
				'wgTwoColConflictBetaFeature' => false,
				'wgTwoColConflictSuggestResolution' => true,
				'userOptionsLookup' => $optOutUser,
				'namespace' => NS_MAIN,
				'expected' => false,
			],
			'disabled on talk pages' => [
				'wgTwoColConflictBetaFeature' => false,
				'wgTwoColConflictSuggestResolution' => false,
				'userOptionsLookup' => $defaultUser,
				'namespace' => NS_TALK,
				'expected' => false,
			],
			'disabled in the project namespace' => [
				'wgTwoColConflictBetaFeature' => false,
				'wgTwoColConflictSuggestResolution' => false,
				'userOptionsLookup' => $defaultUser,
				'namespace' => NS_PROJECT,
				'expected' => false,
			],
		];
	}

	/**
	 * @dataProvider configurationNoBetaFeaturesProvider
	 */
	public function testShouldTwoColConflictBeShown_noBetaFeatures(
		bool $betaConfig,
		bool $singleColumnConfig,
		UserOptionsLookup $userOptionsLookup,
		int $namespace,
		bool $expected
	) {
		$title = $this->createTitle( $namespace );

		$twoColContext = new TwoColConflictContext(
			$this->createConfig( $betaConfig, $singleColumnConfig ),
			$userOptionsLookup,
			$this->createExtensionRegistry( false )
		);
		$result = $twoColContext->shouldTwoColConflictBeShown( $this->createMock( UserIdentity::class ), $title );
		$this->assertSame( $expected, $result );
	}

	public static function configurationNoBetaFeaturesProvider() {
		$defaultUser = self::createUserOptionsLookup();
		$betaUser = self::createUserOptionsLookup( '1', '1' );

		return [
			'enabled in beta mode when BetaFeatures not installed' => [
				'wgTwoColConflictBetaFeature' => true,
				'wgTwoColConflictSuggestResolution' => true,
				'userOptionsLookup' => $defaultUser,
				'namespace' => NS_MAIN,
				'expected' => true,
			],
			'enabled without BetaFeatures, also for an opted-in user' => [
				'wgTwoColConflictBetaFeature' => true,
				'wgTwoColConflictSuggestResolution' => true,
				'userOptionsLookup' => $betaUser,
				'namespace' => NS_MAIN,
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
		/** @var TwoColConflictContext $twoColContext */
		$twoColContext = TestingAccessWrapper::newFromObject( new TwoColConflictContext(
			$this->createConfig( false ),
			self::createUserOptionsLookup( $editingPreference, $betaPreference ),
			$this->createExtensionRegistry()
		) );

		$result = $twoColContext->hasUserEnabledFeature( $this->createMock( UserIdentity::class ) );
		$this->assertSame( $expectedResult, $result );
	}

	public static function provideHasUserEnabledFeature() {
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

	private static function createUserOptionsLookup( string $enabled = '1', ?string $beta = null ): UserOptionsLookup {
		return new StaticUserOptionsLookup( [], [
			TwoColConflictContext::BETA_PREFERENCE_NAME => $beta,
			TwoColConflictContext::ENABLED_PREFERENCE => $enabled,
		] );
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
		$user = new UserIdentityValue( (int)$isRegistered, '' );
		$userOptionsLookup = new StaticUserOptionsLookup( [], [
			TwoColConflictContext::ENABLED_PREFERENCE => $enabledOpt,
			TwoColConflictContext::HIDE_CORE_HINT_PREFERENCE => $hideHintOpt,
		] );

		$twoColContext = new TwoColConflictContext(
			$this->createConfig( $usedAsBeta ),
			$userOptionsLookup,
			$this->createExtensionRegistry()
		);
		$result = $twoColContext->shouldCoreHintBeShown( $user );
		$this->assertSame( $expectedResult, $result );
	}

	public static function provideShouldCoreHintBeShown() {
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

	private function createTitle( int $namespace ): Title {
		$title = $this->createMock( Title::class );
		$title->method( 'hasContentModel' )->willReturn( CONTENT_MODEL_WIKITEXT );
		$title->method( 'isTalkPage' )->willReturn( $namespace === NS_TALK );
		$title->method( 'inNamespace' )->willReturn( $namespace === NS_PROJECT );
		return $title;
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
