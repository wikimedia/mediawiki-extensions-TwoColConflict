var assert = require( 'assert' ),
	VersionPage = require( '../pageobjects/version.page' ),
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

	it( 'is configured correctly', function () {
		VersionPage.open();
		assert( VersionPage.extension.isExisting() );
	} );

	it( 'is showing the edit conflict split screen correctly', function () {
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );

		assert( EditConflictPage.conflictHeader.isExisting() );
		assert( EditConflictPage.conflictView.isExisting() );
	} );

	after( function () {
		browser.deleteCookie();
	} );
} );
