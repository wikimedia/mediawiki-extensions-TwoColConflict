'use strict';

const EditConflictPage = require( '../pageobjects/editconflict.page' ),
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
			await expect(
				EditConflictPage.tourDialog ).toBeDisplayed(
				{ message: 'I see an info tour' }
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
			await expect(
				EditConflictPage.tourDialog ).not.toBeDisplayed(
				{ message: 'I don\'t see an info tour' }
			);
		} );

		it( 'clicking the info button shows the tour', async () => {
			await EditConflictPage.infoButton.click();

			await expect(
				EditConflictPage.tourDialog ).toBeDisplayed(
				{ message: 'I see an info tour' }
			);
		} );

		it( 'clicking the close button dismisses the dialog, adds pulsating buttons, and opens the your version header popup', async () => {
			await EditConflictPage.tourDialogCloseButton.waitForDisplayed();
			await EditConflictPage.tourDialogCloseButton.click();

			await expect(
				EditConflictPage.tourDialogCloseButton ).not.toBeDisplayed(
				{ message: 'Dialog has disappeared' }
			);
			await expect(
				EditConflictPage.tourDiffChangeButton ).toBeDisplayed(
				{ message: 'Diff change pulsating button has appeared' }
			);
			await expect(
				EditConflictPage.tourSplitSelectionButton ).toBeDisplayed(
				{ message: 'Split selection pulsating button has appeared' }
			);
			await expect(
				EditConflictPage.tourYourVersionHeaderPopup ).toBeDisplayed(
				{ message: 'Your version header popup has appeared' }
			);
		} );
	} );

	after( async () => {
		await browser.deleteCookies();
	} );
} );
