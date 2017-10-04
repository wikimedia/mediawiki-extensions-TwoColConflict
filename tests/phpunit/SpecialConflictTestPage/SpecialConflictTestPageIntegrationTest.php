<?php

class SpecialConflictTestPageIntegrationTest extends SpecialPageTestBase {

	/**
	 * Returns a new instance of the special page under test.
	 *
	 * @return SpecialPage
	 */
	protected function newSpecialPage() {
		return new SpecialConflictTestPage();
	}

	public function provideTestData() {
		return [
			'Expect initial input form' => [
				new FauxRequest(),
				function ( $html ) {
					$this->assertFormIsPresent( $html );
					$this->assertTitleInputFieldPresent( $html );
					$this->assertHintBox( $html, 'On this page you can try out the new Two Column Edit Conflict' .
						' interface without messing anything up.' );
				},
			],
			'Expect warning on invalid title' => [
				new FauxRequest( [
					'mw-twocolconflict-test-title' => '!@#$',
				] ),
				function ( $html ) {
					$this->assertFormIsPresent( $html );
					$this->assertTitleInputFieldPresent( $html );
					$this->assertWarningBox( $html, 'There is no page with this title.' );
				},
			],
			'Expect editor on valid title' => [
				new FauxRequest( [
					'mw-twocolconflict-test-title' => 'TestPage',
				] ),
				function ( $html ) {
					$this->assertFormIsPresent( $html );
					$this->assertWikiEditorPresent( $html, "Test content\n" );
				},
				'Test content'
			],
			'Expect conflict page on valid title and edit' => [
				new FauxRequest( [
					'mw-twocolconflict-test-title' => 'TestPage',
					'mw-twocolconflict-test-text' => 'Test content',
				] ),
				function ( $html ) {
					$this->assertTwoColConflictEditorPresent( $html );
					$this->assertHiddenInputField( $html, 'mode', 'conflict' );
					$this->assertHiddenInputField( $html, 'wpUltimateParam', '1' );
					$this->assertHiddenInputField( $html, 'mw-twocolconflict-your-text', 'Test content' );
					$this->assertHiddenInputFieldAny( $html, 'mw-twocolconflict-current-text' );
					$this->assertHiddenInputFieldAny( $html, 'mw-twocolconflict-current-text' );
					$this->assertHintBox(
						$html,
						'This is the test conflict. Changes won\'t be saved but can be previewed.'
					);
				},
				'Test content'
			],
			'Expect preview page on valid title, edit and preview' => [
				new FauxRequest( [
					'wpPreview' => true,
					'wpTextbox1' => 'Test content super duper',
					'wpEditToken' => true,
					'wpUltimateParam' => 1,
					'mw-twocolconflict-title' => 'TestPage',
				] ),
				function ( $html ) {
					$this->assertParserOutputPresentWithContent( $html, "Test content super duper\n" );
				},
			],
		];
	}

	private function assertFormIsPresent( $html ) {
		assertThat(
			$html,
			is( htmlPiece( havingChild(
				both( withTagName( 'form' ) )
					->andAlso( withAttribute( 'action' ) )
					->andAlso( withAttribute( 'method' )->havingValue( 'POST' ) )
					->andAlso( havingChild(
						both( withTagName( 'button' ) )
							->andAlso( withAttribute( 'type' )->havingValue( 'submit' ) )
					) )
			) ) )
		);
	}

	private function assertTitleInputFieldPresent( $html ) {
		assertThat(
			$html,
			is( htmlPiece( havingChild(
				both( withTagName( 'input' ) )
					->andAlso( withAttribute( 'placeholder' )->havingValue( 'Main Page' ) )
					->andAlso( withAttribute( 'name' )->havingValue( 'mw-twocolconflict-test-title' ) )
			) ) )
		);
	}

	private function assertWikiEditorPresent( $html, $text ) {
		assertThat(
			$html,
			is( htmlPiece( havingChild(
				both( withTagName( 'textarea' ) )
					->andAlso( withAttribute( 'name' )->havingValue( 'mw-twocolconflict-test-text' ) )
					->andAlso( havingTextContents( $text ) )
			) ) )
		);
	}

	private function assertTwoColConflictEditorPresent( $html ) {
		assertThat(
			$html,
			is( htmlPiece( havingChild(
				both( withTagName( 'textarea' ) )
					->andAlso( withAttribute( 'name' )->havingValue( 'wpTextbox1' ) )
			) ) )
		);
	}

	private function assertParserOutputPresentWithContent( $html, $text ) {
		assertThat(
			$html,
			is( htmlPiece( havingChild(
				both( withTagName( 'div' ) )
					->andAlso( withClass( 'mw-parser-output' ) )
					->andAlso( havingChild(
						both( withTagName( 'p' ) )
							->andAlso( havingTextContents( $text ) )
					) )
			) ) )
		);
	}

	private function assertWarningBox( $html, $text ) {
		assertThat(
			$html,
			is( htmlPiece( havingChild(
				both( withTagName( 'div' ) )
					->andAlso( withClass( 'warningbox' ) )
					->andAlso( havingChild(
						both( withTagName( 'p' ) )
							->andAlso( havingTextContents( $text ) )
					) )
			) ) )
		);
	}

	private function assertHintBox( $html, $text ) {
		assertThat(
			$html,
			is( htmlPiece( havingChild(
				both( withTagName( 'div' ) )
					->andAlso( withClass( 'mw-twocolconflict-test-hintbox' ) )
					->andAlso( havingChild(
						both( withTagName( 'p' ) )
							->andAlso( havingTextContents( $text ) )
					) )
			) ) )
		);
	}

	private function assertHiddenInputField( $html, $name, $value ) {
		assertThat(
			$html,
			is( htmlPiece( havingChild(
				both( withTagName( 'input' ) )
					->andAlso( withAttribute( 'type' )->havingValue( 'hidden' ) )
					->andAlso( withAttribute( 'name' )->havingValue( $name ) )
					->andAlso( withAttribute( 'value' )->havingValue( $value ) )
			) ) )
		);
	}

	private function assertHiddenInputFieldAny( $html, $name ) {
		assertThat(
			$html,
			is( htmlPiece( havingChild(
				both( withTagName( 'input' ) )
					->andAlso( withAttribute( 'type' )->havingValue( 'hidden' ) )
					->andAlso( withAttribute( 'name' )->havingValue( $name ) )
			) ) )
		);
	}

	/**
	 * @dataProvider provideTestData
	 */
	public function testSpecialPageExecutionWithVariousInputs(
		$request,
		$htmlAssertionCallable,
		$presetText = ''
	) {
		// @codingStandardsIgnoreLine MediaWiki.VariableAnalysis.ForbiddenGlobalVariables.ForbiddenGlobal$wgTitle
		global $wgTitle;

		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', false );

		$user = $this->getTestUser()->getUser();
		$testPageArr = $this->insertPage( 'TestPage', $presetText, NS_MAIN );
		$wgTitle = $testPageArr[ 'title' ];

		/** @var string $html */
		/** @var WebResponse $response */
		list( $html, $response ) = $this->executeSpecialPage(
			'',
			$request,
			'en',
			$user
		);

		$htmlAssertionCallable( $html );
		// assertion to avoid phpunit showing hamcrest test as risky
		$this->assertTrue( true );
	}

	public function testNoPageWhenBetaFeatureAndNoUser() {
		$this->setMwGlobals( 'wgTwoColConflictBetaFeature', true );

		list( $html, $response ) = $this->executeSpecialPage();

		$this->assertEquals( '', $html );
	}
}
