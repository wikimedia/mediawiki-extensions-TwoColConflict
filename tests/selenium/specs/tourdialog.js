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

	describe( 'initial viewing', function () {

		before( function () {
			EditConflictPage.toggleHelpDialog( true );
			EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );
		} );

		it( 'shows the tour', function () {
			assert(
				EditConflictPage.tourDialog.waitForVisible(),
				'I see an info tour'
			);
		} );

		after( function () {
			// provoke and dismiss reload warning
			browser.url( 'data:text/html,Done' );
			try {
				browser.alertAccept();
			} catch ( e ) {}
		} );
	} );

	describe( 'subsequent viewing', function () {

		before( function () {
			EditConflictPage.openTitle( '' );
			EditConflictPage.toggleHelpDialog( false );
			EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );
		} );

		it( 'hides the tour', function () {
			assert(
				!EditConflictPage.tourDialog.isVisible(),
				'I don\'t see an info tour'
			);
		} );

		it( 'clicking the info button shows the tour', function () {
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

	after( function () {
		// provoke and dismiss reload warning
		browser.url( 'data:text/html,Done' );
		try {
			browser.alertAccept();
		} catch ( e ) {
		} finally {
			browser.deleteCookie();
		}
	} );
} );
