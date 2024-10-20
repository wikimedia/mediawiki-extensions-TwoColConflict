<?php

namespace TwoColConflict\Tests;

use MediaWiki\Context\IContextSource;
use MediaWiki\EditPage\EditPage;
use MediaWiki\Language\RawMessage;
use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Request\WebRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\Options\StaticUserOptionsLookup;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserIdentityValue;
use MessageLocalizer;
use OOUI\InputWidget;
use PHPUnit\Framework\MockObject\MockObject;
use TwoColConflict\Hooks\TwoColConflictHooks;
use TwoColConflict\TwoColConflictContext;

/**
 * @covers \TwoColConflict\Hooks\TwoColConflictHooks
 *
 * @group Database
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictHooksTest extends \MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();

		// intentionally not reset in teardown, see Icb6901f4d5
		OutputPage::setupOOUI();

		$this->overrideConfigValues( [
			'TwoColConflictBetaFeature' => false,
			MainConfigNames::LanguageCode => 'qqx',
		] );
	}

	private function getHookHandlerInstance() {
		return new TwoColConflictHooks(
			$this->getServiceContainer()->getService( 'TwoColConflictContext' )
		);
	}

	public function testOnAlternateEdit_withFeatureDisabled() {
		$this->setService( 'UserOptionsLookup', new StaticUserOptionsLookup( [], [
			TwoColConflictContext::ENABLED_PREFERENCE => false,
		] ) );

		$editPage = $this->createNoOpMock( EditPage::class, [ 'getContext' ] );
		$editPage->method( 'getContext' )->willReturn( $this->createContext() );

		$this->getHookHandlerInstance()->onAlternateEdit( $editPage );
	}

	public function testOnAlternateEdit_withInvalidRequest() {
		$request = $this->createMock( WebRequest::class );
		$request->method( 'getArray' )->with( 'mw-twocolconflict-split-content' )->willReturn( [] );
		$request->method( 'getInt' )->with( 'parentRevId' )->willReturn( 1 );
		$request->expects( $this->once() )->method( 'setVal' )->with( 'editRevId', 1 );

		$context = $this->createContext();
		$context->method( 'getRequest' )->willReturn( $request );

		$editPage = $this->createNoOpMock( EditPage::class,
			[ 'getContext', 'setEditConflictHelperFactory' ] );
		$editPage->method( 'getContext' )->willReturn( $context );
		// TODO: The code in the factory function is currently not tested
		$editPage->expects( $this->once() )->method( 'setEditConflictHelperFactory' );

		$this->getHookHandlerInstance()->onAlternateEdit( $editPage );
	}

	public function testOnEditPageBeforeEditButtons() {
		$editPage = $this->createNoOpMock( EditPage::class, [ 'getContext' ] );
		$editPage->isConflict = true;
		$editPage->method( 'getContext' )->willReturn( $this->createContext() );

		$previewButton = $this->createMock( InputWidget::class );
		$previewButton->expects( $this->once() )->method( 'setDisabled' );

		$buttons = [ 'diff' => null, 'preview' => $previewButton ];
		$this->getHookHandlerInstance()->onEditPageBeforeEditButtons( $editPage, $buttons, $tabIndex );
		$this->assertArrayNotHasKey( 'diff', $buttons );
	}

	public function testOnEditPageShowEditFormInitial() {
		$calls = ExtensionRegistry::getInstance()->isLoaded( 'EventLogging' ) ? 1 : 0;
		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->expects( $this->exactly( $calls ) )->method( 'addModules' );

		$this->getHookHandlerInstance()->onEditPage__showEditForm_initial(
			$this->createNoOpMock( EditPage::class ),
			$outputPage
		);
	}

	public function testOnGetBetaFeaturePreferences_whileInBeta() {
		$this->markTestSkippedIfExtensionNotLoaded( 'BetaFeatures' );

		$this->overrideConfigValues( [
			'TwoColConflictBetaFeature' => true,
			MainConfigNames::ExtensionAssetsPath => '',
		] );

		$prefs = [];
		$this->getHookHandlerInstance()->onGetBetaFeaturePreferences( $this->getTestUser()->getUser(), $prefs );
		$this->assertArrayHasKey( TwoColConflictContext::BETA_PREFERENCE_NAME, $prefs );
	}

	public function testOnGetBetaFeaturePreferences_withBetaDisabled() {
		$prefs = [];
		$this->getHookHandlerInstance()->onGetBetaFeaturePreferences( $this->getTestUser()->getUser(), $prefs );
		$this->assertArrayNotHasKey( TwoColConflictContext::BETA_PREFERENCE_NAME, $prefs );
	}

	public function testOnGetPreferences_whileInBeta() {
		$this->markTestSkippedIfExtensionNotLoaded( 'BetaFeatures' );

		$this->overrideConfigValue( 'TwoColConflictBetaFeature', true );

		$prefs = [];
		$this->getHookHandlerInstance()->onGetPreferences( $this->getTestUser()->getUser(), $prefs );
		$this->assertArrayNotHasKey( TwoColConflictContext::ENABLED_PREFERENCE, $prefs );
	}

	public function testOnGetPreferences() {
		$prefs = [];
		$this->getHookHandlerInstance()->onGetPreferences( $this->getTestUser()->getUser(), $prefs );
		$this->assertArrayHasKey( TwoColConflictContext::ENABLED_PREFERENCE, $prefs );
	}

	/**
	 * @return IContextSource|MockObject
	 */
	private function createContext(): IContextSource {
		$context = $this->createMock( IContextSource::class );
		$context->method( 'getTitle' )->willReturn( Title::makeTitle( NS_MAIN, __CLASS__ ) );
		$context->method( 'getUser' )->willReturn( UserIdentityValue::newAnonymous( '' ) );
		return $context;
	}

	/**
	 * Integration for our option hooks and the User class.
	 *
	 * @dataProvider provideGetOption
	 */
	public function testGetOption( ?string $origBeta, ?string $origEditing, bool $expectedEditing ) {
		$this->overrideConfigValue( 'TwoColConflictBetaFeature', false );
		$user = $this->getTestUser()->getUser();

		$this->setOptionRow( $user, TwoColConflictContext::BETA_PREFERENCE_NAME, $origBeta );
		$this->setOptionRow( $user, TwoColConflictContext::ENABLED_PREFERENCE, $origEditing );

		$userOptionsLookup = $this->getServiceContainer()->getUserOptionsLookup();
		$fetchedBeta = $userOptionsLookup->getOption( $user, TwoColConflictContext::BETA_PREFERENCE_NAME );
		$fetchedEditing = $userOptionsLookup->getOption( $user, TwoColConflictContext::ENABLED_PREFERENCE );
		$this->assertNull( $fetchedBeta );
		$this->assertSame( $expectedEditing, (bool)$fetchedEditing );
	}

	public static function provideGetOption() {
		return [
			[
				'origBeta' => '0',
				'origEditing' => '0',
				'newEditing' => false,
			],
			[
				'origBeta' => '0',
				'origEditing' => null,
				'newEditing' => true,
			],
			[
				'origBeta' => '0',
				'origEditing' => '1',
				'newEditing' => true,
			],
			[
				'origBeta' => null,
				'origEditing' => '0',
				'newEditing' => false,
			],
			[
				'origBeta' => null,
				'origEditing' => null,
				'newEditing' => true,
			],
			[
				'origBeta' => null,
				'origEditing' => '1',
				'newEditing' => true,
			],
			[
				'origBeta' => '1',
				'origEditing' => '0',
				'newEditing' => false,
			],
			[
				'origBeta' => '1',
				'origEditing' => null,
				'newEditing' => true,
			],
			[
				'origBeta' => '1',
				'origEditing' => '1',
				'newEditing' => true,
			],
		];
	}

	/**
	 * Integration for our option hooks and the User class.
	 *
	 * @dataProvider provideSetOption
	 */
	public function testSetOption( ?string $origBeta, ?string $origEditing, ?bool $setEditing, bool $newEditing ) {
		$this->overrideConfigValue( 'TwoColConflictBetaFeature', false );
		$user = $this->getTestUser()->getUser();

		$this->setOptionRow( $user, TwoColConflictContext::BETA_PREFERENCE_NAME, $origBeta );
		$this->setOptionRow( $user, TwoColConflictContext::ENABLED_PREFERENCE, $origEditing );

		$userOptionsManager = $this->getServiceContainer()->getUserOptionsManager();
		$userOptionsManager->setOption( $user, TwoColConflictContext::ENABLED_PREFERENCE, $setEditing );
		$userOptionsManager->saveOptions( $user );

		$fetchedBeta = $userOptionsManager->getOption( $user, TwoColConflictContext::BETA_PREFERENCE_NAME );
		$fetchedEditing = $userOptionsManager->getOption( $user, TwoColConflictContext::ENABLED_PREFERENCE );
		$this->assertNull( $fetchedBeta );
		$this->assertSame( $newEditing, (bool)$fetchedEditing );
	}

	public static function provideSetOption() {
		return [
			[
				'origBeta' => null,
				'origEditing' => null,
				'setEditing' => true,
				'newEditing' => true,
			],
			[
				'origBeta' => null,
				'origEditing' => null,
				'setEditing' => false,
				'newEditing' => false,
			],
			[
				'origBeta' => null,
				'origEditing' => null,
				'setEditing' => null,
				'newEditing' => true,
			],
		];
	}

	private function setOptionRow( UserIdentity $user, string $key, ?string $value ) {
		$db = MediaWikiServices::getInstance()->getDBLoadBalancerFactory()->getPrimaryDatabase();
		if ( $value === null ) {
			$db->newDeleteQueryBuilder()
				->deleteFrom( 'user_properties' )
				->where( [
					'up_user' => $user->getId(),
					'up_property' => $key,
				] )
				->caller( __METHOD__ )
				->execute();
		} else {
			$db->newInsertQueryBuilder()
				->insertInto( 'user_properties' )
				->row( [
					'up_user' => $user->getId(),
					'up_property' => $key,
					'up_value' => $value,
				] )
				->caller( __METHOD__ )
				->execute();
		}
	}

	public function testOnEditPageShowEditFormFields() {
		$this->markTestSkippedIfExtensionNotLoaded( 'BetaFeatures' );

		$this->setService( 'UserOptionsLookup', new StaticUserOptionsLookup( [], [
			TwoColConflictContext::ENABLED_PREFERENCE => false,
			// T364741
			'skin' => '',
		] ) );

		$editPage = $this->createNoOpMock( EditPage::class );
		$editPage->isConflict = true;

		$outputPage = $this->createOutputPage();
		$outputPage->expects( $this->once() )
			->method( 'addHTML' )
			->with( $this->stringContains( '(twocolconflict-core-ui-hint)' ) );

		$this->getHookHandlerInstance()->onEditPage__showEditForm_fields( $editPage, $outputPage );
	}

	private function createOutputPage() {
		$context = new class implements MessageLocalizer {
			public function msg( $key, ...$params ) {
				return new RawMessage( "($key)" );
			}
		};

		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->method( 'getUser' )
			->willReturn( UserIdentityValue::newRegistered( 1, '' ) );
		$outputPage->method( 'getContext' )
			->willReturn( $context );

		return $outputPage;
	}

}
