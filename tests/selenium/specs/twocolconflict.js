var assert = require( 'assert' ),
	VersionPage = require( '../pageobjects/version.page' ),
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

	it( 'is configured correctly', function () {
		VersionPage.open();
		assert( VersionPage.extension.isExisting() );
	} );

	it( 'is showing the edit conflict split screen correctly', function () {
		EditConflictPage.showsAnEditConflictWith( conflictUser, conflictUserPassword );

		assert( EditConflictPage.conflictHeader.isExisting() );
		assert( EditConflictPage.conflictView.isExisting() );
	} );

	afterEach( function () {
		browser.deleteCookie();
	} );

} );
