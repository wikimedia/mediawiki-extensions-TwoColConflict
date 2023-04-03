'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' );

describe( 'TwoColConflict GuidedTour', function () {
	before( function () {
		EditConflictPage.prepareEditConflict();
	} );

	describe( 'on initial view', function () {

		before( function () {
			EditConflictPage.toggleHelpDialog( true );
			EditConflictPage.showSimpleConflict();
		} );

		it( 'shows the tour', function () {
			assert(
				EditConflictPage.tourDialog.waitForDisplayed(),
				'I see an info tour'
			);
		} );
	} );

	describe( 'on subsequent view', function () {

		before( function () {
			EditConflictPage.openTitle( '' );
			EditConflictPage.toggleHelpDialog( false );
			EditConflictPage.showSimpleConflict();
		} );

		it( 'hides the tour', function () {
			assert(
				!EditConflictPage.tourDialog.isDisplayed(),
				'I don\'t see an info tour'
			);
		} );

		it( 'clicking the info button shows the tour', function () {
			EditConflictPage.infoButton.click();

			assert(
				EditConflictPage.tourDialog.waitForDisplayed(),
				'I see an info tour'
			);
		} );

		it( 'clicking the close button dismisses the dialog, adds pulsating buttons, and opens the your version header popup', function () {
			EditConflictPage.tourDialogCloseButton.waitForDisplayed();
			EditConflictPage.tourDialogCloseButton.click();

			assert(
				EditConflictPage.tourDialogCloseButton.waitForDisplayed( {
					timeout: 2000,
					reverse: true
				} ),
				'Dialog has disappeared'
			);

			assert(
				EditConflictPage.tourDiffChangeButton.isDisplayed(),
				'Diff change pulsating button has appeared'
			);

			assert(
				EditConflictPage.tourSplitSelectionButton.isDisplayed(),
				'Split selection pulsating button has appeared'
			);

			assert(
				EditConflictPage.tourYourVersionHeaderPopup.isDisplayed(),
				'Your version header popup has appeared'
			);
		} );
	} );

	after( function () {
		browser.deleteCookies();
	} );
} );
