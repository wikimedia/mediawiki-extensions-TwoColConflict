var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	Util = require( 'wdio-mediawiki/Util' );

describe( 'TwoColConflict', function () {
	let conflictUser,
		conflictUserPassword;

	before( function () {
		conflictUser = Util.getTestString( 'User-' );
		conflictUserPassword = Util.getTestString();
		EditConflictPage.prepareEditConflict( conflictUser, conflictUserPassword );
	} );

	beforeEach( function () {
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );

		assert( EditConflictPage.submitButton.isVisible(), 'submit button exists' );
		assert( EditConflictPage.previewButton.isVisible(), 'preview button exists' );
		assert( !EditConflictPage.diffButton.isVisible(), 'no diff button' );
	} );

	it( 'has edit buttons that toggle availability depending on side selection', function () {
		EditConflictPage.yourParagraphSelection.click();

		assert(
			EditConflictPage.getEditButton( 'your' ).getAttribute( 'class' )
				.indexOf( 'oo-ui-widget-disabled' ) === -1,
			'I see an activated edit icon on the selected "yours" paragraph'
		);
		assert(
			EditConflictPage.getEditButton( 'other' ).getAttribute( 'class' )
				.indexOf( 'oo-ui-widget-disabled' ) !== -1,
			'I see a deactivated edit icon on the selected "mine" paragraph'
		);
		assert(
			EditConflictPage.getEditButton( 'unchanged' ).getAttribute( 'class' )
				.indexOf( 'oo-ui-widget-disabled' ) === -1,
			'I see an activated edit icon on the unchanged paragraph'
		);
	} );

	it( 'allows editing of conflict paragraphs by clicking the activated edit button', function () {
		EditConflictPage.getEditButton( 'other' ).click();

		assert(
			EditConflictPage.getEditor( 'other' ).waitForVisible(),
			'the selected text box becomes a wikitext editor'
		);
		assert(
			!EditConflictPage.getEditor( 'your' ).isVisible(),
			'the unselected text box stays as it is'
		);
		assert(
			!EditConflictPage.getEditButton( 'other' ).isVisible(),
			'the edit icon disappears in the selected text box'
		);
		assert(
			EditConflictPage.getEditButton( 'your' ).isVisible(),
			'the edit icon in the unselected text box stays as it is'
		);
		assert(
			!EditConflictPage.getEditor( 'unchanged' ).isVisible(),
			'the unselected unchanged text box stays as it is'
		);
		assert(
			EditConflictPage.getParagraph( 'your' ).getAttribute( 'class' )
				.indexOf( 'mw-editfont-monospace' ) !== -1,
			'the layout changes to wikitext editor layout for both paragraphs'
		);
		assert(
			EditConflictPage.getParagraph( 'unchanged' ).getAttribute( 'class' )
				.indexOf( 'mw-editfont-monospace' ) === -1,
			'the layout stays the same for the unselected unchanged text box'
		);
	} );

	it( 'allows editing of unchanged paragraphs by clicking the activated edit button', function () {
		EditConflictPage.getEditButton( 'unchanged' ).click();
		assert(
			!EditConflictPage.getEditor( 'other' ).isVisible(),
			'the selected text box stays as it is'
		);
		assert(
			!EditConflictPage.getEditor( 'your' ).isVisible(),
			'the unselected text box stays as it is'
		);
		assert(
			EditConflictPage.getEditButton( 'other' ).isVisible(),
			'the edit icon in the selected text box stays as it is'
		);
		assert(
			EditConflictPage.getEditButton( 'your' ).isVisible(),
			'the edit icon in the unselected text box stays as it is'
		);
		assert(
			EditConflictPage.getEditor( 'unchanged' ).isVisible(),
			'the unselected unchanged text box becomes a wikitext editor'
		);
		assert(
			!EditConflictPage.getEditButton( 'unchanged' ).isVisible(),
			'the edit icon disappears in the unchanged text box'
		);
		assert(
			EditConflictPage.getParagraph( 'other' ).getAttribute( 'class' )
				.indexOf( 'mw-editfont-monospace' ) === -1,
			'the layout stays the same for the selected text box'
		);
		assert(
			EditConflictPage.getParagraph( 'your' ).getAttribute( 'class' )
				.indexOf( 'mw-editfont-monospace' ) === -1,
			'the layout stays the same for the unselected text box'
		);
		assert(
			EditConflictPage.getParagraph( 'unchanged' ).getAttribute( 'class' )
				.indexOf( 'mw-editfont-monospace' ) !== -1,
			'the layout changes to wikitext editor layout'
		);
	} );

	it( 'certain edit specific buttons should not be visible at first', function () {
		assert(
			!EditConflictPage.getSaveButton( 'unchanged' ).isVisible(),
			'the edit icon in the unselected unchanged text box is hidden'
		);
		assert(
			!EditConflictPage.getSaveButton( 'other' ).isVisible(),
			'the edit icon in the selected text box is hidden'
		);
		assert(
			!EditConflictPage.getSaveButton( 'your' ).isVisible(),
			'the edit icon in the unselected text box is hidden'
		);

		assert(
			!EditConflictPage.getResetButton( 'unchanged' ).isVisible(),
			'the reset icon in the unselected unchanged text box is hidden'
		);
		assert(
			!EditConflictPage.getResetButton( 'other' ).isVisible(),
			'the reset icon in the selected text box is hidden'
		);
		assert(
			!EditConflictPage.getResetButton( 'your' ).isVisible(),
			'the reset icon in the unselected text box is hidden'
		);
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

		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getEditor( 'other' ).setValue( 'Dummy Edit #1' );
		EditConflictPage.getSaveButton( 'other' ).click();

		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getEditor( 'other' ).setValue( 'Dummy Edit #2' );
		EditConflictPage.getSaveButton( 'other' ).click();

		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getResetButton( 'other' ).click();
		EditConflictPage.resetConfirmationPopup.waitForVisible( 1000 );
		EditConflictPage.resetConfirmationButton.click();
		EditConflictPage.resetConfirmationButton.waitForVisible( 1000, true );

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
	} );

	it( 'clicking edit should automatically focus the text editor', function () {
		EditConflictPage.getEditButton( 'unchanged' ).click();
		assert(
			EditConflictPage.getEditor( 'unchanged' ).hasFocus(),
			'text editor is focused'
		);
	} );

	it( 'hovering over a disabled edit button should display a popup for that button only', function () {
		EditConflictPage.hoverEditButton( 'your' );
		assert(
			EditConflictPage.getEditDisabledEditButtonPopup( 'your' ).isVisible(),
			'popup is now visible for the disabled edit button'
		);
		assert(
			!EditConflictPage.getEditDisabledEditButtonPopup( 'other' ).isVisible(),
			'popup is still hidden for the enabled edit button'
		);
	} );

	it( 'hovering away from a disabled edit button should hide the popup', function () {
		EditConflictPage.hoverEditButton( 'your' );
		assert(
			EditConflictPage.getEditDisabledEditButtonPopup( 'your' ).isVisible(),
			'popup is now visible for the disabled edit button'
		);

		EditConflictPage.hoverEditButton( 'other' );
		assert(
			!EditConflictPage.getEditDisabledEditButtonPopup( 'your' ).isVisible(),
			'popup is now hidden for the disabled edit button'
		);
	} );

	it( 'hovering over an enabled edit button should not display a popup', function () {
		EditConflictPage.hoverEditButton( 'other' );
		assert(
			!EditConflictPage.getEditDisabledEditButtonPopup( 'your' ).isVisible(),
			'edit button popup is not visible'
		);
		assert(
			!EditConflictPage.getEditDisabledEditButtonPopup( 'other' ).isVisible(),
			'edit button popup is not visible'
		);
	} );

	afterEach( function () {
		// provoke and dismiss reload warning
		browser.url( 'data:text/html,Done' );
		try {
			browser.alertAccept();
		} catch ( e ) {}
	} );

	after( function () {
		browser.deleteCookie();
	} );
} );
