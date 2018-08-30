var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	Api = require( 'wdio-mediawiki/Api' ),
	Util = require( 'wdio-mediawiki/Util' );

describe( 'TwoColConflict', function () {
	var conflictUser,
		conflictUserPassword;

	before( function () {
		conflictUser = Util.getTestString( 'User-' );
		conflictUserPassword = Util.getTestString();
		browser.call( function () {
			Api.createAccount( conflictUser, conflictUserPassword );
		} );
	} );

	beforeEach( function () {
		EditConflictPage.showsAnEditConflictWith( conflictUser, conflictUserPassword );
	} );

	it( 'has edit buttons that toggle availability depending on side selection', function () {
		EditConflictPage.yourParagraphSelection.click();

		assert(
			EditConflictPage.yourParagraphEditButton.getAttribute( 'class' )
				.indexOf( 'oo-ui-widget-disabled' ) === -1,
			'I see an activated edit icon on the selected "yours" paragraph'
		);
		assert(
			EditConflictPage.otherParagraphEditButton.getAttribute( 'class' )
				.indexOf( 'oo-ui-widget-disabled' ) !== -1,
			'I see a deactivated edit icon on the selected "mine" paragraph'
		);
		assert(
			EditConflictPage.unchangedParagraphEditButton.getAttribute( 'class' )
				.indexOf( 'oo-ui-widget-disabled' ) === -1,
			'I see an activated edit icon on the unchanged paragraph'
		);
	} );

	afterEach( function () {
		browser.deleteCookie();
	} );

} );
