var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
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

	after( function () {
		browser.deleteCookie();
	} );
} );
