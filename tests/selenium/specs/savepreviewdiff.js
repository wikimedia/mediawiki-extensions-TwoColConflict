var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	FinishedConflictPage = require( '../pageobjects/finishedconflict.page' ),
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
	} );

	it( 'should resolve the conflict successfully', function () {
		EditConflictPage.submitButton.click();

		assert.strictEqual(
			FinishedConflictPage.pageText.getText(),
			'Line1 Change A',
			'text was saved correctly'
		);
	} );

	it( 'should resolve the conflict successfully when unsaved edits in selected paragraphs are present', function () {
		EditConflictPage.yourParagraphSelection.click();
		EditConflictPage.getEditButton( 'your' ).click();
		EditConflictPage.getEditor( 'your' ).setValue( 'Dummy Text' );
		EditConflictPage.submitButton.click();

		assert.strictEqual(
			FinishedConflictPage.pageText.getText(),
			'Line1 Dummy Text',
			'text was saved correctly'
		);
	} );

	it( 'should show a preview page', function () {
		EditConflictPage.previewButton.click();

		assert(
			EditConflictPage.previewView.waitForVisible(),
			'I see a preview page for my changes'
		);

		assert.strictEqual(
			EditConflictPage.previewText.getText(),
			'Line1 Change A',
			'text preview shows correctly'
		);
	} );

	it( 'should show a correct preview page when unsaved edits in selected paragraphs are present', function () {
		EditConflictPage.yourParagraphSelection.click();
		EditConflictPage.getEditButton( 'your' ).click();
		EditConflictPage.getEditor( 'your' ).setValue( 'Dummy Text' );
		EditConflictPage.previewButton.click();

		assert(
			EditConflictPage.previewView.waitForVisible(),
			'I see a preview page for my changes'
		);

		assert.strictEqual(
			EditConflictPage.previewText.getText(),
			'Line1 Dummy Text',
			'text was saved correctly'
		);
	} );

	it( 'should be possible to edit and preview the left ("other") side', function () {
		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getEditor( 'other' ).setValue( 'Other, but improved' );
		EditConflictPage.previewButton.click();

		assert(
			EditConflictPage.previewView.waitForVisible(),
			'The preview appears'
		);

		assert.strictEqual(
			EditConflictPage.previewText.getText(),
			'Line1 Other, but improved',
			'My edit appears in the preview'
		);

		assert.strictEqual(
			EditConflictPage.getEditor( 'other' ).getValue(),
			'Other, but improved',
			'I can continue the edit I started'
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
