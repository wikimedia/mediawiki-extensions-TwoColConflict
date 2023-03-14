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
			EditConflictPage.tourDialogCloseButton.waitForDisplayed( { timeout: 2000 } );
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

		it( 'clicking on a pulsating button opens a popup', function () {
			// FIXME tourDiffChangeButton.click() throws an error with the new wdio config this is a workaround
			browser.execute( function () {
				$( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-pulsating-button' ).click();
			} );

			EditConflictPage.tourDiffChangeButton.waitForDisplayed( {
				timeout: 2000, reverse: true
			} );

			assert(
				!EditConflictPage.tourDiffChangeButton.isDisplayed(),
				'Diff change pulsating button has disappeared'
			);

			assert(
				EditConflictPage.tourDiffChangePopup.waitForDisplayed( { timeout: 2000 } ),
				'Diff change popup has appeared'
			);
		} );

		it( 'clicking on a popup\'s close button closes the popup', function () {
			EditConflictPage.tourDiffChangePopupCloseButton.waitForDisplayed( { timeout: 2000 } );
			EditConflictPage.tourDiffChangePopupCloseButton.click();

			assert(
				EditConflictPage.tourDiffChangePopup.waitForDisplayed( {
					timeout: 500, reverse: true
				} ),
				'Diff change popup has disappeared'
			);
		} );
	} );

	after( function () {
		browser.deleteCookies();
	} );
} );
