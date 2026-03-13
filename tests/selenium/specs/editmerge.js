'use strict';

const EditConflictPage = require( '../pageobjects/editconflict.page' );

describe( 'TwoColConflict EditUi', () => {
	before( async () => {
		await EditConflictPage.prepareEditConflict();
	} );

	beforeEach( async () => {
		await EditConflictPage.showSimpleConflict();
	} );

	it( 'will switch to edit mode by clicking the column that is selected', async () => {
		await EditConflictPage.otherParagraphSelection.click();
		await EditConflictPage.getColumn( 'other' ).click();

		await expect(
			EditConflictPage.getEditor( 'other' ) ).toBeDisplayed(
			{ message: 'the selected other text box becomes a wikitext editor' }
		);
		await expect(
			EditConflictPage.getEditor( 'other' ) ).toBeFocused(
			{ message: 'text editor is focused' }
		);
		await expect(
			EditConflictPage.getEditor( 'your' ) ).not.toBeDisplayed(
			{ message: 'the unselected your text box stays as it is' }
		);
		await expect(
			EditConflictPage.getEditor( 'unchanged' ) ).not.toBeDisplayed(
			{ message: 'the unselected unchanged text box stays as it is' }
		);
	} );

	it( 'edits of unchanged paragraphs should be saved', async () => {
		const unchangedParagraphNewText = 'Dummy Text';

		await EditConflictPage.getEditButton( 'unchanged' ).click();
		await EditConflictPage.getEditor( 'unchanged' ).setValue( unchangedParagraphNewText );
		await EditConflictPage.getSaveButton( 'unchanged' ).click();
		await expect(
			await EditConflictPage.getDiffText( 'unchanged' ).getText() ).toBe(
			unchangedParagraphNewText,
			{ message: 'unchanged text diff was edited' }
		);

		await expect(
			await EditConflictPage.getEditor( 'unchanged' ).getValue() ).toBe(
			unchangedParagraphNewText,
			{ message: 'unchanged text editor was edited' }
		);
	} );

	it( 'paragraph edits can be reverted', async () => {
		const otherParagraphOriginalDiffText = await EditConflictPage.getDiffText( 'other' ).getHTML(),
			otherParagraphOriginalText = await EditConflictPage.getEditor( 'other' ).getValue();

		await EditConflictPage.otherParagraphSelection.click();

		await EditConflictPage.getEditButton( 'other' ).click();
		await EditConflictPage.getEditor( 'other' ).setValue( 'Dummy Edit #1' );
		await EditConflictPage.getSaveButton( 'other' ).click();

		await EditConflictPage.getEditButton( 'other' ).click();
		await EditConflictPage.getEditor( 'other' ).setValue( 'Dummy Edit #2' );
		await EditConflictPage.getSaveButton( 'other' ).click();

		await EditConflictPage.getEditButton( 'other' ).click();
		await EditConflictPage.getResetButton( 'other' ).click();
		await EditConflictPage.resetConfirmationPopup.waitForDisplayed();
		await EditConflictPage.resetConfirmationButton.click();
		await EditConflictPage.resetConfirmationButton.waitForDisplayed( {
			timeout: 2000,
			reverse: true
		} );

		await expect(
			await EditConflictPage.getDiffText( 'other' ).getHTML() ).toBe(
			otherParagraphOriginalDiffText,
			{ message: 'edited text was reverted successfully while preserving the formatting' }
		);

		await expect(
			await EditConflictPage.getEditor( 'other' ).getValue() ).toBe(
			otherParagraphOriginalText,
			{ message: 'plain text in editor was reverted successfully' }
		);

		await expect(
			await EditConflictPage.getEditor( 'other' ) ).not.toBeDisplayed(
			{ message: 'the editor is hidden again and we left editing mode' }
		);
	} );

	it( 'saving an editor with no changes will preserve the highlight portions', async () => {
		const otherParagraphOriginalDiffText = await EditConflictPage.getDiffText( 'other' ).getHTML();

		await EditConflictPage.otherParagraphSelection.click();

		await EditConflictPage.getEditButton( 'other' ).click();
		await EditConflictPage.getSaveButton( 'other' ).click();

		await expect(
			await EditConflictPage.getDiffText( 'other' ).getHTML() ).toBe(
			otherParagraphOriginalDiffText,
			{ message: 'edited text was unchanged hence the formatting was preserved' }
		);
	} );

	after( async () => {
		await browser.deleteCookies();
	} );
} );
