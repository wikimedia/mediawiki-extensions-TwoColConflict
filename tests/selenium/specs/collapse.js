'use strict';

const EditConflictPage = require( '../pageobjects/editconflict.page' );

describe( 'TwoColConflict collapse button', function () {
	before( async function () {
		await EditConflictPage.prepareEditConflict();
	} );

	beforeEach( async function () {
		await EditConflictPage.showBigConflict();
	} );

	it( 'collapses and expands long unchanged paragraphs', async function () {
		await EditConflictPage.assertUnchangedIsCollapsed();
		await EditConflictPage.expandButton.click();
		await EditConflictPage.assertUnchangedIsExpanded();
		await EditConflictPage.collapseButton.click();
		await EditConflictPage.assertUnchangedIsCollapsed();
	} );

	it( 'expands collapsed paragraphs after editing or aborting edits', async function () {
		const unchangedParagraphNewText = 'Dummy Text';

		await EditConflictPage.getEditButton( 'unchanged' ).waitForDisplayed();
		await EditConflictPage.getEditButton( 'unchanged' ).click();

		await EditConflictPage.getEditor( 'unchanged' ).waitForDisplayed();
		await EditConflictPage.getEditor( 'unchanged' ).setValue( unchangedParagraphNewText );

		await EditConflictPage.getResetButton( 'unchanged' ).waitForDisplayed();
		await EditConflictPage.getResetButton( 'unchanged' ).click();

		await EditConflictPage.resetConfirmationPopup.waitForDisplayed();
		await EditConflictPage.resetConfirmationButton.click();
		await EditConflictPage.resetConfirmationButton.waitForDisplayed( {
			timeout: 2000,
			reverse: true
		} );

		await EditConflictPage.assertUnchangedIsExpanded();

		await EditConflictPage.collapseButton.click();
		await EditConflictPage.assertUnchangedIsCollapsed();

		await EditConflictPage.getEditButton( 'unchanged' ).click();

		await EditConflictPage.getEditor( 'unchanged' ).waitForDisplayed();
		await EditConflictPage.getEditor( 'unchanged' ).setValue( unchangedParagraphNewText );
		await EditConflictPage.getSaveButton( 'unchanged' ).click();

		await EditConflictPage.assertUnchangedIsExpanded();
	} );

	after( async function () {
		await browser.deleteCookies();
	} );
} );
