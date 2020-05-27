var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	PreferencesPage = require( '../pageobjects/preferences.page' );

describe( 'TwoColConflict', function () {
	before( function () {
		EditConflictPage.prepareEditConflict();
	} );

	it( 'is showing the edit conflict split screen correctly', function () {
		EditConflictPage.showSimpleConflict();

		assert( EditConflictPage.conflictHeader.isExisting() );
		assert( EditConflictPage.conflictView.isExisting() );
	} );

	it( 'labels change according to selected column', function () {
		EditConflictPage.showSimpleConflict();

		const initialText = EditConflictPage.selectionLabel.getText();

		EditConflictPage.yourParagraphSelection.click();

		const yourSelectionText = EditConflictPage.selectionLabel.getText();

		assert(
			initialText !== yourSelectionText,
			'Your side is selected when you click the row\'s radio button'
		);

		EditConflictPage.otherParagraphAllSelection.click();

		const otherSelectionText = EditConflictPage.selectionLabel.getText();

		assert(
			yourSelectionText !== otherSelectionText && initialText !== otherSelectionText,
			'The other side is selected when you click the other side\'s select all button'
		);
	} );

	it( 'is not used when it is not enabled in the preferences', function () {
		PreferencesPage.shouldUseTwoColConflictBetaFeature( false );
		PreferencesPage.shouldUseTwoColConflict( false );
		EditConflictPage.createConflict(
			'A',
			'B',
			'C'
		);

		assert(
			EditConflictPage.wpTextbox2.isDisplayed(),
			'the editor for the core conflict UI is shown'
		);
		assert(
			!EditConflictPage.conflictHeader.isExisting() &&
			!EditConflictPage.conflictView.isExisting(),
			'the two column UI is not loaded'
		);
	} );

	it( 'shows a dismissible hint on the core edit conflict interface', function () {
		PreferencesPage.openEditPreferences();
		if ( !PreferencesPage.hasOptOutUserSetting() ) {
			this.skip();
		}
		PreferencesPage.shouldUseTwoColConflict( false );
		PreferencesPage.resetCoreHintVisibility();
		EditConflictPage.createConflict(
			'A',
			'B',
			'C'
		);

		assert(
			EditConflictPage.coreUiHint.isDisplayed(),
			'the core conflict UI shows a hint to enable the new interface'
		);

		EditConflictPage.waitForJS();
		EditConflictPage.coreUiHintCloseButton.click();

		assert(
			!EditConflictPage.coreUiHint.isDisplayed(),
			'the hint on the core conflict is hidden'
		);

		EditConflictPage.createConflict(
			'A',
			'B',
			'C'
		);

		assert(
			!EditConflictPage.coreUiHint.isDisplayed(),
			'the hint on the core conflict is hidden on the next conflict'
		);
	} );

	after( function () {
		browser.deleteCookies();
	} );
} );
