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
		EditConflictPage.testNoJs();
	} );

	it( 'is showing the nojs version correctly', function () {
		EditConflictPage.createConflict(
			conflictUser,
			conflictUserPassword,
			'A',
			'B',
			'C'
		);
		// wait for the nojs script to switch CSS visibility
		EditConflictPage.getEditor( 'your' ).waitForVisible();

		assert( EditConflictPage.conflictHeader.isVisible() );
		assert( EditConflictPage.conflictView.isVisible() );
		assert(
			EditConflictPage.yourParagraphRadio.isSelected() &&
			!EditConflictPage.otherParagraphRadio.isSelected(),
			'your side is selected by default'
		);
		assert(
			EditConflictPage.getEditor( 'your' ).isVisible() &&
			EditConflictPage.getEditor( 'other' ).isVisible(),
			'editors are visible right away'
		);
	} );

	after( function () {
		browser.deleteCookie();
	} );
} );
