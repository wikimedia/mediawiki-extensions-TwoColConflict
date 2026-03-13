'use strict';

const EditConflictPage = require( '../pageobjects/editconflict.page' ),
	FinishedConflictPage = require( '../pageobjects/finishedconflict.page' );

describe( 'TwoColConflict save and preview', () => {
	before( async () => {
		await EditConflictPage.prepareEditConflict();
	} );

	it( 'should save a resolved conflict successfully including changes', async () => {
		await EditConflictPage.showSimpleConflict();

		await EditConflictPage.yourParagraphSelection.click();
		await EditConflictPage.getEditButton( 'your' ).click();
		await EditConflictPage.getEditor( 'your' ).setValue( 'Dummy Text' );
		await EditConflictPage.submitButton.click();

		await expect(
			await FinishedConflictPage.pageWikitext() ).toBe(
			'Line<span>1</span>\n\nDummy Text'
		);
	} );

	it( 'should show a correct preview page including changes', async () => {
		await EditConflictPage.showSimpleConflict();

		await EditConflictPage.yourParagraphSelection.click();
		await EditConflictPage.getEditButton( 'your' ).click();
		// make sure that pre-save transforms are rendered correctly
		await EditConflictPage.getEditor( 'your' ).setValue( 'Dummy Text [[title (topic)|]]' );
		await EditConflictPage.previewButton.click();

		await expect(
			EditConflictPage.previewView ).toBeDisplayed(
			{ message: 'I see a preview page for my changes' }
		);
		expect(
			await EditConflictPage.previewText.getText() ).toBe(
			'Line1\n\nDummy Text title'
		);
	} );

	after( async () => {
		await browser.deleteCookies();
	} );
} );
