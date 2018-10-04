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

	describe( 'initial viewing', function () {

		before( function () {
			EditConflictPage.showsAnEditConflictWith( conflictUser, conflictUserPassword, false );
		} );

		after( function () {
			// provoke and dismiss reload warning
			browser.url( 'data:' );
			try {
				browser.alertAccept();
			} catch ( e ) {
			} finally {
				browser.deleteCookie();
			}
		} );

		it( 'shows the tour', function () {
			assert(
				EditConflictPage.tourDialog.waitForVisible(),
				'I see an info tour'
			);
		} );
	} );

	describe( 'subsequent viewing', function () {

		before( function () {
			EditConflictPage.showsAnEditConflictWith( conflictUser, conflictUserPassword );
		} );

		after( function () {
			// provoke and dismiss reload warning
			browser.url( 'data:' );
			try {
				browser.alertAccept();
			} catch ( e ) {
			} finally {
				browser.deleteCookie();
			}
		} );

		it( 'hides the tour', function () {
			assert(
				!EditConflictPage.tourDialog.isVisible(),
				'I don\'t see an info tour'
			);
		} );

		it( 'clicking the info button shows the tour', function () {
			assert(
				EditConflictPage.infoButton.waitForVisible(),
				'I see an info button'
			);

			EditConflictPage.infoButton.click();

			assert(
				EditConflictPage.tourDialog.waitForVisible(),
				'I see an info tour'
			);
		} );

		it( 'clicking the close button dismisses the dialog and adds pulsating buttons', function () {
			EditConflictPage.tourDialogCloseButton.waitForVisible( 1000 );
			EditConflictPage.tourDialogCloseButton.click();

			assert(
				EditConflictPage.tourDialogCloseButton.waitForVisible( 2000, true ),
				'Dialog has disappeared'
			);

			assert(
				EditConflictPage.tourDiffChangeButton.isVisible(),
				'Diff change pulsating button has appeared'
			);

			assert(
				EditConflictPage.tourSplitSelectionButton.isVisible(),
				'Split selection pulsating button has appeared'
			);

			assert(
				EditConflictPage.tourYourVersionHeaderButton.isVisible(),
				'Your version header pulsating button has appeared'
			);
		} );

		it( 'clicking on a pulsating button opens a popup', function () {
			EditConflictPage.tourDiffChangeButton.waitForVisible( 1000 );
			EditConflictPage.tourDiffChangeButton.click();

			assert(
				!EditConflictPage.tourDiffChangeButton.isVisible(),
				'Diff change pulsating button has disappeared'
			);

			assert(
				EditConflictPage.tourDiffChangePopup.waitForVisible(),
				'Diff change popup has appeared'
			);
		} );

		it( 'clicking on a popup\'s close button closes the popup', function () {
			EditConflictPage.tourDiffChangePopupCloseButton.waitForVisible( 1000 );
			EditConflictPage.tourDiffChangePopupCloseButton.click();

			assert(
				EditConflictPage.tourDiffChangePopup.waitForVisible( 500, true ),
				'Diff change popup has disappeared'
			);
		} );
	} );
} );
