var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	Api = require( 'wdio-mediawiki/Api' ),
	Util = require( 'wdio-mediawiki/Util' );

describe( 'TwoColConflict', function () {
	var conflictUser,
		conflictUserPassword;

	before( function () {
		conflictUser = Util.getTestString( 'User-' );
		conflictUserPassword = Util.getTestString();
		browser.call( function () {
			Api.createAccount( conflictUser, conflictUserPassword );
		} );
	} );

	beforeEach( function () {
		EditConflictPage.showsAnEditConflictWith( conflictUser, conflictUserPassword );
	} );

	it( 'has edit buttons that toggle availability depending on side selection', function () {
		EditConflictPage.yourParagraphSelection.click();

		assert(
			EditConflictPage.yourParagraphEditButton.getAttribute( 'class' )
				.indexOf( 'oo-ui-widget-disabled' ) === -1,
			'I see an activated edit icon on the selected "yours" paragraph'
		);
		assert(
			EditConflictPage.otherParagraphEditButton.getAttribute( 'class' )
				.indexOf( 'oo-ui-widget-disabled' ) !== -1,
			'I see a deactivated edit icon on the selected "mine" paragraph'
		);
		assert(
			EditConflictPage.unchangedParagraphEditButton.getAttribute( 'class' )
				.indexOf( 'oo-ui-widget-disabled' ) === -1,
			'I see an activated edit icon on the unchanged paragraph'
		);
	} );

	it( 'allows editing of conflict paragraphs by clicking the activated edit button', function () {
		EditConflictPage.otherParagraphEditButton.click();

		assert(
			EditConflictPage.otherParagraphEditor.isVisible(),
			'the selected text box becomes a wikitext editor'
		);
		assert(
			!EditConflictPage.yourParagraphEditor.isVisible(),
			'the unselected text box stays as it is'
		);
		assert(
			!EditConflictPage.otherParagraphEditButton.isVisible(),
			'the edit icon disappears in the selected text box'
		);
		assert(
			EditConflictPage.yourParagraphEditButton.isVisible(),
			'the edit icon in the unselected text box stays as it is'
		);
		assert(
			!EditConflictPage.unchangedParagraphEditor.isVisible(),
			'the unselected unchanged text box stays as it is'
		);
		assert.strictEqual(
			EditConflictPage.yourParagraph.getCssProperty( '-webkit-appearance' ).value,
			'textarea',
			'the layout changes to wikitext editor layout for both paragraphs'
		);
		assert.strictEqual(
			EditConflictPage.unchangedParagraph.getCssProperty( '-webkit-appearance' ).value,
			'none',
			'the layout stays the same for the unselected unchanged text box'
		);
	} );

	it( 'allows editing of unchanged paragraphs by clicking the activated edit button', function () {
		EditConflictPage.unchangedParagraphEditButton.click();

		assert(
			!EditConflictPage.otherParagraphEditor.isVisible(),
			'the selected text box stays as it is'
		);
		assert(
			!EditConflictPage.yourParagraphEditor.isVisible(),
			'the unselected text box stays as it is'
		);
		assert(
			EditConflictPage.otherParagraphEditButton.isVisible(),
			'the edit icon in the selected text box stays as it is'
		);
		assert(
			EditConflictPage.yourParagraphEditButton.isVisible(),
			'the edit icon in the unselected text box stays as it is'
		);
		assert(
			EditConflictPage.unchangedParagraphEditor.isVisible(),
			'the unselected unchanged text box becomes a wikitext editor'
		);
		assert(
			!EditConflictPage.unchangedParagraphEditButton.isVisible(),
			'the edit icon disappears in the unchanged text box'
		);
		assert.strictEqual(
			EditConflictPage.otherParagraph.getCssProperty( '-webkit-appearance' ).value,
			'none',
			'the layout stays the same for the selected text box'
		);
		assert.strictEqual(
			EditConflictPage.yourParagraph.getCssProperty( '-webkit-appearance' ).value,
			'none',
			'the layout stays the same for the unselected text box'
		);
		assert.strictEqual(
			EditConflictPage.unchangedParagraph.getCssProperty( '-webkit-appearance' ).value,
			'textarea',
			'the layout changes to wikitext editor layout'
		);
	} );

	afterEach( function () {
		// provoke and dismiss reload warning
		browser.url( 'data:' );
		try {
			browser.alertAccept();
		} finally {
			browser.deleteCookie();
		}
	} );

} );
