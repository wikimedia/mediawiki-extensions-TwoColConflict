'use strict';

const EditConflictPage = require( '../pageobjects/editconflict.page' );

describe( 'TwoColConflict EditUi', () => {
	before( async () => {
		await EditConflictPage.prepareEditConflict();
	} );

	beforeEach( async () => {
		await EditConflictPage.showSimpleConflict();
	} );

	describe( 'on initial view', () => {

		before( async () => {
			await EditConflictPage.showSimpleConflict();
		} );

		it( 'will not switch to edit mode as long as nothing is selected', async () => {
			await EditConflictPage.getColumn( 'other' ).click();
			await EditConflictPage.getColumn( 'your' ).click();
			await EditConflictPage.getColumn( 'unchanged' ).click();
			await expect(
				EditConflictPage.getEditButton( 'other' ) ).toHaveAttributeContaining(
				'class',
				'oo-ui-element-hidden',
				{ message: '"other" edit button should be hidden' }
			);
			await expect(
				EditConflictPage.getEditButton( 'your' ) ).toHaveAttributeContaining(
				'class',
				'oo-ui-element-hidden',
				{ message: '"your" edit button should be hidden' }
			);

			await expect(
				EditConflictPage.rowsInEditMode ).not.toBeDisplayed(
				{ message: 'no row is in edit mode' }
			);

			await expect(
				EditConflictPage.getSaveButton( 'unchanged' ) ).not.toBeDisplayed(
				{ message: 'the edit icon in the unselected unchanged text box is hidden' }
			);
			await expect(
				EditConflictPage.getSaveButton( 'other' ) ).not.toBeDisplayed( {
				message: 'the edit icon in the selected text box is hidden' }
			);
			await expect(
				EditConflictPage.getSaveButton( 'your' ) ).not.toBeDisplayed(
				{ message: 'the edit icon in the unselected text box is hidden' }
			);

			await expect(
				EditConflictPage.getResetButton( 'unchanged' ) ).not.toBeDisplayed(
				{ message: 'the reset icon in the unselected unchanged text box is hidden' }
			);
			await expect(
				EditConflictPage.getResetButton( 'other' ) ).not.toBeDisplayed(
				{ message: 'the reset icon in the selected text box is hidden' }
			);
			await expect(
				EditConflictPage.getResetButton( 'your' ) ).not.toBeDisplayed(
				{ message: 'the reset icon in the unselected text box is hidden' }
			);
		} );

		it( 'has edit buttons that toggle visibility depending on the side selection', async () => {
			await EditConflictPage.yourParagraphSelection.click();

			await expect(
				EditConflictPage.getEditButton( 'your' ) ).not.toHaveAttributeContaining(
				'class',
				'oo-ui-element-hidden',
				{ message: 'I see an activated edit icon on the selected "yours" paragraph' }
			);
			await expect(
				EditConflictPage.getEditButton( 'other' ) ).toHaveAttributeContaining(
				'class',
				'oo-ui-element-hidden',
				{ message: 'I don\'t see an edit icon on the selected "mine" paragraph' }
			);
			await expect(
				EditConflictPage.getEditButton( 'unchanged' ) ).not.toHaveAttributeContaining(
				'class',
				'oo-ui-element-hidden',
				{ message: 'I see an activated edit icon on the unchanged paragraph' }
			);
		} );

		it( 'will not switch to edit mode if the column clicked is not selected', async () => {
			await EditConflictPage.otherParagraphSelection.click();
			await EditConflictPage.getColumn( 'your' ).click();

			await expect(
				EditConflictPage.getEditor( 'other' ) ).not.toBeDisplayed(
				{ message: 'the selected other text box stays as it is' }
			);
			await expect(
				EditConflictPage.getEditor( 'unchanged' ) ).not.toBeDisplayed(
				{ message: 'the unselected unchanged text box stays as it is' }
			);
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

		it( 'will switch to edit mode by clicking the edit button in the column that is selected', async () => {
			await EditConflictPage.otherParagraphSelection.click();

			await EditConflictPage.getEditButton( 'other' ).click();

			await expect(
				EditConflictPage.getEditor( 'other' ) ).toBeDisplayed(
				{ message: 'the selected text box becomes a wikitext editor' }
			);
			await expect(
				EditConflictPage.getEditor( 'other' ) ).toBeFocused(
				{ message: 'text editor is focused' }
			);
			await expect(
				EditConflictPage.getEditor( 'your' ) ).not.toBeDisplayed(
				{ message: 'the unselected text box stays as it is' }
			);
			await expect(
				EditConflictPage.getEditButton( 'other' ) ).not.toBeDisplayed(
				{ message: 'the edit icon disappears in the selected text box' }
			);
			await expect(
				EditConflictPage.getEditButton( 'your' ) ).not.toBeDisplayed(
				{ message: 'the edit icon in the unselected text box stays hidden' }
			);
			await expect(
				EditConflictPage.getEditor( 'unchanged' ) ).not.toBeDisplayed(
				{ message: 'the unselected unchanged text box stays as it is' }
			);
			await expect(
				EditConflictPage.getParagraph( 'your' ) ).toHaveAttributeContaining(
				'class',
				'mw-editfont-monospace',
				{ message: 'the layout changes to wikitext editor layout for both paragraphs' }
			);
			await expect(
				EditConflictPage.getParagraph( 'unchanged' ) ).not.toHaveAttributeContaining(
				'class',
				'mw-editfont-monospace',
				{ message: 'the layout stays the same for the unselected unchanged text box' }
			);
		} );

		it( 'will switch to edit mode by clicking the edit button in unchanged paragraphs', async () => {
			await EditConflictPage.getEditButton( 'unchanged' ).click();

			await expect(
				EditConflictPage.getEditor( 'other' ) ).not.toBeDisplayed(
				{ message: 'the selected text box stays as it is' }
			);
			await expect(
				EditConflictPage.getEditor( 'your' ) ).not.toBeDisplayed(
				{ message: 'the unselected text box stays as it is' }
			);
			await expect(
				EditConflictPage.getEditButton( 'other' ) ).not.toBeDisplayed(
				{ message: 'the edit icon in the selected text box stays hidden' }
			);
			await expect(
				EditConflictPage.getEditButton( 'your' ) ).not.toBeDisplayed(
				{ message: 'the edit icon in the unselected text box stays hidden' }
			);
			await expect(
				EditConflictPage.getEditor( 'unchanged' ) ).toBeDisplayed(
				{ message: 'the unselected unchanged text box becomes a wikitext editor' }
			);
			await expect(
				EditConflictPage.getEditButton( 'unchanged' ) ).not.toBeDisplayed(
				{ message: 'the edit icon disappears in the unchanged text box' }
			);
			await expect(
				EditConflictPage.getParagraph( 'other' ) ).not.toHaveAttributeContaining(
				'class',
				'mw-editfont-monospace',
				{ message: 'the layout stays the same for the selected text box' }
			);
			await expect(
				EditConflictPage.getParagraph( 'your' ) ).not.toHaveAttributeContaining(
				'class',
				'mw-editfont-monospace',
				{ message: 'the layout stays the same for the unselected text box' }
			);
			await expect(
				EditConflictPage.getParagraph( 'unchanged' ) ).toHaveAttributeContaining(
				'class',
				'mw-editfont-monospace',
				{ message: 'the layout changes to wikitext editor layout' }
			);
		} );
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

	it( 'edits of selected paragraphs should be saved and should not affect unselected paragraphs', async () => {
		const yourParagraphDiffText = await EditConflictPage.getDiffText( 'your' ).getText(),
			yourParagraphEditorText = await EditConflictPage.getEditor( 'your' ).getValue(),
			otherParagraphNewText = 'Dummy Text';

		await EditConflictPage.otherParagraphSelection.click();

		await EditConflictPage.getEditButton( 'other' ).click();
		await EditConflictPage.getEditor( 'other' ).setValue( otherParagraphNewText );
		await EditConflictPage.getSaveButton( 'other' ).click();

		await expect(
			await EditConflictPage.getDiffText( 'your' ).getText() ).toBe(
			yourParagraphDiffText,
			{ message: 'unselected text diff was not edited' }
		);

		await expect(
			await EditConflictPage.getEditor( 'your' ).getValue() ).toBe(
			yourParagraphEditorText,
			{ message: 'unselected text editor was not edited' }
		);

		await expect(
			await EditConflictPage.getDiffText( 'other' ).getText() ).toBe(
			otherParagraphNewText,
			{ message: 'selected text diff was edited' }
		);

		await expect(
			await EditConflictPage.getEditor( 'other' ).getValue() ).toBe(
			otherParagraphNewText,
			{ message: 'selected text editor was edited' }
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

	it( 'revert confirmation will not show if nothing changed', async () => {
		await EditConflictPage.otherParagraphSelection.click();

		await EditConflictPage.getEditButton( 'other' ).click();
		await EditConflictPage.getResetButton( 'other' ).click();
		await EditConflictPage.resetConfirmationButton.waitForDisplayed( {
			timeout: 2000,
			reverse: true
		} );

		await expect(
			EditConflictPage.resetConfirmationButton ).not.toBeDisplayed(
			{ message: 'there is no confirmation box for the reset visible' }
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
