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

	it( 'label changes according to selected column', function () {
		EditConflictPage.showSimpleConflict();

		const initialText = EditConflictPage.selectionLabel.getText();

		EditConflictPage.yourParagraphSelection.click();

		const updatedText = EditConflictPage.selectionLabel.getText();

		assert( initialText !== updatedText );
	} );

	it( 'is not used when it is not enabled in the preferences', function () {
		PreferencesPage.openEditPreferences();
		if ( !this.hasOptOutUserSetting ) {
			this.skip();
		}
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

	after( function () {
		browser.deleteAllCookies();
	} );
} );
