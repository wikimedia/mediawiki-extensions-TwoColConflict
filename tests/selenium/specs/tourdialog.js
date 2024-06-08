'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	TestAccounts = require( '../test_accounts' );

describe( 'TwoColConflict GuidedTour', () => {
	before( async () => {
		await TestAccounts.loginAsUser();
		await EditConflictPage.prepareUserSettings();
	} );

	describe( 'on initial view', () => {

		before( async () => {
			await EditConflictPage.toggleHelpDialog( true );
			await EditConflictPage.showSimpleConflict();
		} );

		it( 'shows the tour', async () => {
			assert(
				await EditConflictPage.tourDialog.waitForDisplayed(),
				'I see an info tour'
			);
		} );
	} );

	describe( 'on subsequent view', () => {

		before( async () => {
			await EditConflictPage.openTitle( '' );
			await EditConflictPage.toggleHelpDialog( false );
			await EditConflictPage.showSimpleConflict();
		} );

		it( 'hides the tour', async () => {
			assert(
				!( await EditConflictPage.tourDialog.isDisplayed() ),
				'I don\'t see an info tour'
			);
		} );

		it( 'clicking the info button shows the tour', async () => {
			await EditConflictPage.infoButton.click();

			assert(
				await EditConflictPage.tourDialog.waitForDisplayed(),
				'I see an info tour'
			);
		} );

		it( 'clicking the close button dismisses the dialog, adds pulsating buttons, and opens the your version header popup', async () => {
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

	after( async () => {
		await browser.deleteCookies();
	} );
} );
