'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' );

describe( 'TwoColConflict GuidedTour', function () {
	before( async function () {
		await EditConflictPage.prepareEditConflict();
	} );

	describe( 'on initial view', function () {

		before( async function () {
			await EditConflictPage.toggleHelpDialog( true );
			await EditConflictPage.showSimpleConflict();
		} );

		it( 'shows the tour', async function () {
			assert(
				await EditConflictPage.tourDialog.waitForDisplayed(),
				'I see an info tour'
			);
		} );
	} );

	describe( 'on subsequent view', function () {

		before( async function () {
			await EditConflictPage.openTitle( '' );
			await EditConflictPage.toggleHelpDialog( false );
			await EditConflictPage.showSimpleConflict();
		} );

		it( 'hides the tour', async function () {
			assert(
				!( await EditConflictPage.tourDialog.isDisplayed() ),
				'I don\'t see an info tour'
			);
		} );

		it( 'clicking the info button shows the tour', async function () {
			await EditConflictPage.infoButton.click();

			assert(
				await EditConflictPage.tourDialog.waitForDisplayed(),
				'I see an info tour'
			);
		} );

		it( 'clicking the close button dismisses the dialog, adds pulsating buttons, and opens the your version header popup', async function () {
			await EditConflictPage.tourDialogCloseButton.waitForDisplayed();
			await EditConflictPage.tourDialogCloseButton.click();

			assert(
				await EditConflictPage.tourDialogCloseButton.waitForDisplayed( {
					timeout: 2000,
					reverse: true
				} ),
				'Dialog has disappeared'
			);

			assert(
				await EditConflictPage.tourDiffChangeButton.isDisplayed(),
				'Diff change pulsating button has appeared'
			);

			assert(
				await EditConflictPage.tourSplitSelectionButton.isDisplayed(),
				'Split selection pulsating button has appeared'
			);

			assert(
				await EditConflictPage.tourYourVersionHeaderPopup.isDisplayed(),
				'Your version header popup has appeared'
			);
		} );
	} );

	after( async function () {
		await browser.deleteCookies();
	} );
} );
