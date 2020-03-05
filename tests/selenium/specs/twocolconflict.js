var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	PreferencesPage = require( '../pageobjects/preferences.page' ),
	Util = require( 'wdio-mediawiki/Util' );

describe( 'TwoColConflict', function () {
	let conflictUser,
		conflictUserPassword;

	before( function () {
		conflictUser = Util.getTestString( 'User-' );
		conflictUserPassword = Util.getTestString();
		EditConflictPage.prepareEditConflict( conflictUser, conflictUserPassword );
	} );

	it( 'is showing the edit conflict split screen correctly', function () {
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );

		assert( EditConflictPage.conflictHeader.isExisting() );
		assert( EditConflictPage.conflictView.isExisting() );
	} );

	it( 'label changes according to selected column', function () {
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );

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
			conflictUser,
			conflictUserPassword,
			'A',
			'B',
			'C'
		);

		assert(
			EditConflictPage.wpTextbox2.isVisible(),
			'the editor for the core conflict UI is shown'
		);
		assert(
			!EditConflictPage.conflictHeader.isExisting() &&
			!EditConflictPage.conflictView.isExisting(),
			'the two column UI is not loaded'
		);
	} );

	after( function () {
		browser.deleteCookie();
	} );
} );
