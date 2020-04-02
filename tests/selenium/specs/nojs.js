var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' );

describe( 'TwoColConflict', function () {
	before( function () {
		EditConflictPage.prepareEditConflict();
		EditConflictPage.testNoJs();
	} );

	it( 'is showing the nojs version correctly', function () {
		EditConflictPage.createConflict(
			'A',
			'B',
			'C'
		);
		// wait for the nojs script to switch CSS visibility
		EditConflictPage.getEditor( 'your' ).waitForDisplayed();

		assert( EditConflictPage.conflictHeader.isDisplayed() );
		assert( EditConflictPage.conflictView.isDisplayed() );
		assert(
			EditConflictPage.yourParagraphRadio.isSelected() &&
			!EditConflictPage.otherParagraphRadio.isSelected(),
			'your side is selected by default'
		);
		assert(
			EditConflictPage.getEditor( 'your' ).isDisplayed() &&
			EditConflictPage.getEditor( 'other' ).isDisplayed(),
			'editors are visible right away'
		);
	} );

	after( function () {
		browser.deleteAllCookies();
	} );
} );
