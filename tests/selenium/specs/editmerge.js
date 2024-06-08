'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' );

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
			assert(
				( await EditConflictPage.getEditButton( 'other' ).getAttribute( 'class' ) )
					.includes( 'oo-ui-element-hidden' ) &&
				( await EditConflictPage.getEditButton( 'your' ).getAttribute( 'class' ) )
					.includes( 'oo-ui-element-hidden' ),
				'neither side is activated'
			);

			assert(
				await EditConflictPage.rowsInEditMode.waitForDisplayed( { reverse: true } ),
				'no row is in edit mode'
			);
			assert(
				!( await EditConflictPage.getSaveButton( 'unchanged' ).isDisplayed() ),
				'the edit icon in the unselected unchanged text box is hidden'
			);
			assert(
				!( await EditConflictPage.getSaveButton( 'other' ).isDisplayed() ),
				'the edit icon in the selected text box is hidden'
			);
			assert(
				!( await EditConflictPage.getSaveButton( 'your' ).isDisplayed() ),
				'the edit icon in the unselected text box is hidden'
			);

			assert(
				!( await EditConflictPage.getResetButton( 'unchanged' ).isDisplayed() ),
				'the reset icon in the unselected unchanged text box is hidden'
			);
			assert(
				!( await EditConflictPage.getResetButton( 'other' ).isDisplayed() ),
				'the reset icon in the selected text box is hidden'
			);
			assert(
				!( await EditConflictPage.getResetButton( 'your' ).isDisplayed() ),
				'the reset icon in the unselected text box is hidden'
			);
		} );

		it( 'has edit buttons that toggle visibility depending on the side selection', async () => {
			await EditConflictPage.yourParagraphSelection.click();

			assert(
				!( await EditConflictPage.getEditButton( 'your' ).getAttribute( 'class' ) )
					.includes( 'oo-ui-element-hidden' ),
				'I see an activated edit icon on the selected "yours" paragraph'
			);
			assert(
				( await EditConflictPage.getEditButton( 'other' ).getAttribute( 'class' ) )
					.includes( 'oo-ui-element-hidden' ),
				'I don\'t see an edit icon on the selected "mine" paragraph'
			);
			assert(
				!( await EditConflictPage.getEditButton( 'unchanged' ).getAttribute( 'class' ) )
					.includes( 'oo-ui-element-hidden' ),
				'I see an activated edit icon on the unchanged paragraph'
			);
		} );

		it( 'will not switch to edit mode if the column clicked is not selected', async () => {
			await EditConflictPage.otherParagraphSelection.click();
			await EditConflictPage.getColumn( 'your' ).click();

			assert(
				await EditConflictPage.getEditor( 'other' ).waitForDisplayed( { reverse: true } ),
				'the selected other text box stays as it is'
			);
			assert(
				!( await EditConflictPage.getEditor( 'unchanged' ).isDisplayed() ),
				'the unselected unchanged text box stays as it is'
			);
		} );

		it( 'will switch to edit mode by clicking the column that is selected', async () => {
			await EditConflictPage.otherParagraphSelection.click();
			await EditConflictPage.getColumn( 'other' ).click();

			assert(
				await EditConflictPage.getEditor( 'other' ).waitForDisplayed(),
				'the selected other text box becomes a wikitext editor'
			);
			assert(
				await EditConflictPage.getEditor( 'other' ).isFocused(),
				'text editor is focused'
			);
			assert(
				!( await EditConflictPage.getEditor( 'your' ).isDisplayed() ),
				'the unselected your text box stays as it is'
			);
			assert(
				!( await EditConflictPage.getEditor( 'unchanged' ).isDisplayed() ),
				'the unselected unchanged text box stays as it is'
			);
		} );

		it( 'will switch to edit mode by clicking the edit button in the column that is selected', async () => {
			await EditConflictPage.otherParagraphSelection.click();

			await EditConflictPage.getEditButton( 'other' ).click();

			assert(
				await EditConflictPage.getEditor( 'other' ).waitForDisplayed(),
				'the selected text box becomes a wikitext editor'
			);
			assert(
				await EditConflictPage.getEditor( 'other' ).isFocused(),
				'text editor is focused'
			);
			assert(
				!( await EditConflictPage.getEditor( 'your' ).isDisplayed() ),
				'the unselected text box stays as it is'
			);
			assert(
				!( await EditConflictPage.getEditButton( 'other' ).isDisplayed() ),
				'the edit icon disappears in the selected text box'
			);
			assert(
				!( await EditConflictPage.getEditButton( 'your' ).isDisplayed() ),
				'the edit icon in the unselected text box stays hidden'
			);
			assert(
				!( await EditConflictPage.getEditor( 'unchanged' ).isDisplayed() ),
				'the unselected unchanged text box stays as it is'
			);
			assert(
				( await EditConflictPage.getParagraph( 'your' ).getAttribute( 'class' ) )
					.includes( 'mw-editfont-monospace' ),
				'the layout changes to wikitext editor layout for both paragraphs'
			);
			assert(
				!( await EditConflictPage.getParagraph( 'unchanged' ).getAttribute( 'class' ) )
					.includes( 'mw-editfont-monospace' ),
				'the layout stays the same for the unselected unchanged text box'
			);
		} );

		it( 'will switch to edit mode by clicking the edit button in unchanged paragraphs', async () => {
			await EditConflictPage.getEditButton( 'unchanged' ).click();
			assert(
				!( await EditConflictPage.getEditor( 'other' ).isDisplayed() ),
				'the selected text box stays as it is'
			);
			assert(
				!( await EditConflictPage.getEditor( 'your' ).isDisplayed() ),
				'the unselected text box stays as it is'
			);
			assert(
				!( await EditConflictPage.getEditButton( 'other' ).isDisplayed() ),
				'the edit icon in the selected text box stays hidden'
			);
			assert(
				!( await EditConflictPage.getEditButton( 'your' ).isDisplayed() ),
				'the edit icon in the unselected text box stays hidden'
			);
			assert(
				await EditConflictPage.getEditor( 'unchanged' ).isDisplayed(),
				'the unselected unchanged text box becomes a wikitext editor'
			);
			assert(
				!( await EditConflictPage.getEditButton( 'unchanged' ).isDisplayed() ),
				'the edit icon disappears in the unchanged text box'
			);
			assert(
				!( await EditConflictPage.getParagraph( 'other' ).getAttribute( 'class' ) )
					.includes( 'mw-editfont-monospace' ),
				'the layout stays the same for the selected text box'
			);
			assert(
				!( await EditConflictPage.getParagraph( 'your' ).getAttribute( 'class' ) )
					.includes( 'mw-editfont-monospace' ),
				'the layout stays the same for the unselected text box'
			);
			assert(
				( await EditConflictPage.getParagraph( 'unchanged' ).getAttribute( 'class' ) )
					.includes( 'mw-editfont-monospace' ),
				'the layout changes to wikitext editor layout'
			);
		} );
	} );

	it( 'edits of unchanged paragraphs should be saved', async () => {
		const unchangedParagraphNewText = 'Dummy Text';

		await EditConflictPage.getEditButton( 'unchanged' ).click();
		await EditConflictPage.getEditor( 'unchanged' ).setValue( unchangedParagraphNewText );
		await EditConflictPage.getSaveButton( 'unchanged' ).click();

		assert.strictEqual(
			await EditConflictPage.getDiffText( 'unchanged' ).getText(),
			unchangedParagraphNewText,
			'unchanged text diff was edited'
		);

		assert.strictEqual(
			await EditConflictPage.getEditor( 'unchanged' ).getValue(),
			unchangedParagraphNewText,
			'unchanged text editor was edited'
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

		assert.strictEqual(
			await EditConflictPage.getDiffText( 'your' ).getText(),
			yourParagraphDiffText,
			'unselected text diff was not edited'
		);

		assert.strictEqual(
			await EditConflictPage.getEditor( 'your' ).getValue(),
			yourParagraphEditorText,
			'unselected text editor was not edited'
		);

		assert.strictEqual(
			await EditConflictPage.getDiffText( 'other' ).getText(),
			otherParagraphNewText,
			'selected text diff was edited'
		);

		assert.strictEqual(
			await EditConflictPage.getEditor( 'other' ).getValue(),
			otherParagraphNewText,
			'selected text editor was edited'
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

		assert.strictEqual(
			await EditConflictPage.getDiffText( 'other' ).getHTML(),
			otherParagraphOriginalDiffText,
			'edited text was reverted successfully while preserving the formatting'
		);

		assert.strictEqual(
			await EditConflictPage.getEditor( 'other' ).getValue(),
			otherParagraphOriginalText,
			'plain text in editor was reverted successfully'
		);
		assert(
			!( await EditConflictPage.getEditor( 'other' ).isDisplayed() ),
			'the editor is hidden again and we left editing mode'
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
		assert(
			!( await EditConflictPage.resetConfirmationButton.isDisplayed() ),
			'there is no confirmation box for the reset visible'
		);
		assert(
			!( await EditConflictPage.getEditor( 'other' ).isDisplayed() ),
			'the editor is hidden again and we left editing mode'
		);
	} );

	it( 'saving an editor with no changes will preserve the highlight portions', async () => {
		const otherParagraphOriginalDiffText = await EditConflictPage.getDiffText( 'other' ).getHTML();

		await EditConflictPage.otherParagraphSelection.click();

		await EditConflictPage.getEditButton( 'other' ).click();
		await EditConflictPage.getSaveButton( 'other' ).click();

		assert.strictEqual(
			await EditConflictPage.getDiffText( 'other' ).getHTML(),
			otherParagraphOriginalDiffText,
			'edited text was unchanged hence the formatting was preserved'
		);
	} );

	after( async () => {
		await browser.deleteCookies();
	} );
} );
