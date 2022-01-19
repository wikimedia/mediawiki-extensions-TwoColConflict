'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' );

describe( 'TwoColConflict EditUi', function () {
	before( function () {
		EditConflictPage.prepareEditConflict();
	} );

	beforeEach( function () {
		EditConflictPage.showSimpleConflict();
	} );

	describe( 'on initial view', function () {

		before( function () {
			EditConflictPage.showSimpleConflict();
		} );

		it( 'will not switch to edit mode as long as nothing is selected', function () {
			EditConflictPage.getColumn( 'other' ).click();
			EditConflictPage.getColumn( 'your' ).click();
			EditConflictPage.getColumn( 'unchanged' ).click();

			assert(
				EditConflictPage.getEditButton( 'other' ).getAttribute( 'class' )
					.includes( 'oo-ui-element-hidden' ) &&
				EditConflictPage.getEditButton( 'your' ).getAttribute( 'class' )
					.includes( 'oo-ui-element-hidden' ),
				'neither side is activated'
			);

			assert(
				EditConflictPage.rowsInEditMode.waitForDisplayed( { reverse: true } ),
				'no row is in edit mode'
			);
			assert(
				!EditConflictPage.getSaveButton( 'unchanged' ).isDisplayed(),
				'the edit icon in the unselected unchanged text box is hidden'
			);
			assert(
				!EditConflictPage.getSaveButton( 'other' ).isDisplayed(),
				'the edit icon in the selected text box is hidden'
			);
			assert(
				!EditConflictPage.getSaveButton( 'your' ).isDisplayed(),
				'the edit icon in the unselected text box is hidden'
			);

			assert(
				!EditConflictPage.getResetButton( 'unchanged' ).isDisplayed(),
				'the reset icon in the unselected unchanged text box is hidden'
			);
			assert(
				!EditConflictPage.getResetButton( 'other' ).isDisplayed(),
				'the reset icon in the selected text box is hidden'
			);
			assert(
				!EditConflictPage.getResetButton( 'your' ).isDisplayed(),
				'the reset icon in the unselected text box is hidden'
			);
		} );

		it( 'has edit buttons that toggle visibility depending on the side selection', function () {
			EditConflictPage.yourParagraphSelection.click();

			assert(
				!EditConflictPage.getEditButton( 'your' ).getAttribute( 'class' )
					.includes( 'oo-ui-element-hidden' ),
				'I see an activated edit icon on the selected "yours" paragraph'
			);
			assert(
				EditConflictPage.getEditButton( 'other' ).getAttribute( 'class' )
					.includes( 'oo-ui-element-hidden' ),
				'I don\'t see an edit icon on the selected "mine" paragraph'
			);
			assert(
				!EditConflictPage.getEditButton( 'unchanged' ).getAttribute( 'class' )
					.includes( 'oo-ui-element-hidden' ),
				'I see an activated edit icon on the unchanged paragraph'
			);
		} );

		it( 'will not switch to edit mode if the column clicked is not selected', function () {
			EditConflictPage.otherParagraphSelection.click();
			EditConflictPage.getColumn( 'your' ).click();

			assert(
				EditConflictPage.getEditor( 'other' ).waitForDisplayed( { reverse: true } ),
				'the selected other text box stays as it is'
			);
			assert(
				!EditConflictPage.getEditor( 'unchanged' ).isDisplayed(),
				'the unselected unchanged text box stays as it is'
			);
		} );

		it( 'will switch to edit mode by clicking the column that is selected', function () {
			EditConflictPage.otherParagraphSelection.click();
			EditConflictPage.getColumn( 'other' ).click();

			assert(
				EditConflictPage.getEditor( 'other' ).waitForDisplayed(),
				'the selected other text box becomes a wikitext editor'
			);
			assert(
				EditConflictPage.getEditor( 'other' ).isFocused(),
				'text editor is focused'
			);
			assert(
				!EditConflictPage.getEditor( 'your' ).isDisplayed(),
				'the unselected your text box stays as it is'
			);
			assert(
				!EditConflictPage.getEditor( 'unchanged' ).isDisplayed(),
				'the unselected unchanged text box stays as it is'
			);
		} );

		it( 'will switch to edit mode by clicking the edit button in the column that is selected', function () {
			EditConflictPage.otherParagraphSelection.click();

			EditConflictPage.getEditButton( 'other' ).click();

			assert(
				EditConflictPage.getEditor( 'other' ).waitForDisplayed(),
				'the selected text box becomes a wikitext editor'
			);
			assert(
				EditConflictPage.getEditor( 'other' ).isFocused(),
				'text editor is focused'
			);
			assert(
				!EditConflictPage.getEditor( 'your' ).isDisplayed(),
				'the unselected text box stays as it is'
			);
			assert(
				!EditConflictPage.getEditButton( 'other' ).isDisplayed(),
				'the edit icon disappears in the selected text box'
			);
			assert(
				!EditConflictPage.getEditButton( 'your' ).isDisplayed(),
				'the edit icon in the unselected text box stays hidden'
			);
			assert(
				!EditConflictPage.getEditor( 'unchanged' ).isDisplayed(),
				'the unselected unchanged text box stays as it is'
			);
			assert(
				EditConflictPage.getParagraph( 'your' ).getAttribute( 'class' )
					.includes( 'mw-editfont-monospace' ),
				'the layout changes to wikitext editor layout for both paragraphs'
			);
			assert(
				!EditConflictPage.getParagraph( 'unchanged' ).getAttribute( 'class' )
					.includes( 'mw-editfont-monospace' ),
				'the layout stays the same for the unselected unchanged text box'
			);
		} );

		it( 'will switch to edit mode by clicking the edit button in unchanged paragraphs', function () {
			EditConflictPage.getEditButton( 'unchanged' ).click();
			assert(
				!EditConflictPage.getEditor( 'other' ).isDisplayed(),
				'the selected text box stays as it is'
			);
			assert(
				!EditConflictPage.getEditor( 'your' ).isDisplayed(),
				'the unselected text box stays as it is'
			);
			assert(
				!EditConflictPage.getEditButton( 'other' ).isDisplayed(),
				'the edit icon in the selected text box stays hidden'
			);
			assert(
				!EditConflictPage.getEditButton( 'your' ).isDisplayed(),
				'the edit icon in the unselected text box stays hidden'
			);
			assert(
				EditConflictPage.getEditor( 'unchanged' ).isDisplayed(),
				'the unselected unchanged text box becomes a wikitext editor'
			);
			assert(
				!EditConflictPage.getEditButton( 'unchanged' ).isDisplayed(),
				'the edit icon disappears in the unchanged text box'
			);
			assert(
				!EditConflictPage.getParagraph( 'other' ).getAttribute( 'class' )
					.includes( 'mw-editfont-monospace' ),
				'the layout stays the same for the selected text box'
			);
			assert(
				!EditConflictPage.getParagraph( 'your' ).getAttribute( 'class' )
					.includes( 'mw-editfont-monospace' ),
				'the layout stays the same for the unselected text box'
			);
			assert(
				EditConflictPage.getParagraph( 'unchanged' ).getAttribute( 'class' )
					.includes( 'mw-editfont-monospace' ),
				'the layout changes to wikitext editor layout'
			);
		} );
	} );

	it( 'edits of unchanged paragraphs should be saved', function () {
		const unchangedParagraphNewText = 'Dummy Text';

		EditConflictPage.getEditButton( 'unchanged' ).click();
		EditConflictPage.getEditor( 'unchanged' ).setValue( unchangedParagraphNewText );
		EditConflictPage.getSaveButton( 'unchanged' ).click();

		assert.strictEqual(
			EditConflictPage.getDiffText( 'unchanged' ).getText(),
			unchangedParagraphNewText,
			'unchanged text diff was edited'
		);

		assert.strictEqual(
			EditConflictPage.getEditor( 'unchanged' ).getValue(),
			unchangedParagraphNewText,
			'unchanged text editor was edited'
		);
	} );

	it( 'edits of selected paragraphs should be saved and should not affect unselected paragraphs', function () {
		const yourParagraphDiffText = EditConflictPage.getDiffText( 'your' ).getText(),
			yourParagraphEditorText = EditConflictPage.getEditor( 'your' ).getValue(),
			otherParagraphNewText = 'Dummy Text';

		EditConflictPage.otherParagraphSelection.click();

		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getEditor( 'other' ).setValue( otherParagraphNewText );
		EditConflictPage.getSaveButton( 'other' ).click();

		assert.strictEqual(
			EditConflictPage.getDiffText( 'your' ).getText(),
			yourParagraphDiffText,
			'unselected text diff was not edited'
		);

		assert.strictEqual(
			EditConflictPage.getEditor( 'your' ).getValue(),
			yourParagraphEditorText,
			'unselected text editor was not edited'
		);

		assert.strictEqual(
			EditConflictPage.getDiffText( 'other' ).getText(),
			otherParagraphNewText,
			'selected text diff was edited'
		);

		assert.strictEqual(
			EditConflictPage.getEditor( 'other' ).getValue(),
			otherParagraphNewText,
			'selected text editor was edited'
		);
	} );

	it( 'paragraph edits can be reverted', function () {
		const otherParagraphOriginalDiffText = EditConflictPage.getDiffText( 'other' ).getHTML(),
			otherParagraphOriginalText = EditConflictPage.getEditor( 'other' ).getValue();

		EditConflictPage.otherParagraphSelection.click();

		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getEditor( 'other' ).setValue( 'Dummy Edit #1' );
		EditConflictPage.getSaveButton( 'other' ).click();

		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getEditor( 'other' ).setValue( 'Dummy Edit #2' );
		EditConflictPage.getSaveButton( 'other' ).click();

		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getResetButton( 'other' ).click();
		EditConflictPage.resetConfirmationPopup.waitForDisplayed( { timeout: 2000 } );
		EditConflictPage.resetConfirmationButton.click();
		EditConflictPage.resetConfirmationButton.waitForDisplayed( {
			timeout: 2000,
			reverse: true
		} );

		assert.strictEqual(
			EditConflictPage.getDiffText( 'other' ).getHTML(),
			otherParagraphOriginalDiffText,
			'edited text was reverted successfully while preserving the formatting'
		);

		assert.strictEqual(
			EditConflictPage.getEditor( 'other' ).getValue(),
			otherParagraphOriginalText,
			'plain text in editor was reverted successfully'
		);
		assert(
			!EditConflictPage.getEditor( 'other' ).isDisplayed(),
			'the editor is hidden again and we left editing mode'
		);
	} );

	it( 'revert confirmation will not show if nothing changed', function () {
		EditConflictPage.otherParagraphSelection.click();

		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getResetButton( 'other' ).click();
		EditConflictPage.resetConfirmationButton.waitForDisplayed( {
			timeout: 2000,
			reverse: true
		} );
		assert(
			!EditConflictPage.resetConfirmationButton.isDisplayed(),
			'there is no confirmation box for the reset visible'
		);
		assert(
			!EditConflictPage.getEditor( 'other' ).isDisplayed(),
			'the editor is hidden again and we left editing mode'
		);
	} );

	it( 'saving an editor with no changes will preserve the highlight portions', function () {
		const otherParagraphOriginalDiffText = EditConflictPage.getDiffText( 'other' ).getHTML();

		EditConflictPage.otherParagraphSelection.click();

		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getSaveButton( 'other' ).click();

		assert.strictEqual(
			EditConflictPage.getDiffText( 'other' ).getHTML(),
			otherParagraphOriginalDiffText,
			'edited text was unchanged hence the formatting was preserved'
		);
	} );

	after( function () {
		browser.deleteCookies();
	} );
} );
