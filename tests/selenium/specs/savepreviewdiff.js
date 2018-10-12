var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	FinishedConflictPage = require( '../pageobjects/finishedconflict.page' ),
	PreviewPage = require( '../pageobjects/preview.page' ),
	DiffPage = require( '../pageobjects/diff.page' ),
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
		EditConflictPage.prepareEditConflict();
	} );

	beforeEach( function () {
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );
	} );

	it( 'should resolve the conflict successfully', function () {
		EditConflictPage.submitButton.click();

		assert.strictEqual(
			FinishedConflictPage.pageText.getText(),
			'Line1 ChangeA',
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
			PreviewPage.previewView.waitForVisible(),
			'I see a preview page for my changes'
		);

		assert.strictEqual(
			PreviewPage.previewText.getText(),
			'Line1 ChangeA',
			'text preview shows correctly'
		);
	} );

	it( 'should show a correct preview page when unsaved edits in selected paragraphs are present', function () {
		EditConflictPage.yourParagraphSelection.click();
		EditConflictPage.getEditButton( 'your' ).click();
		EditConflictPage.getEditor( 'your' ).setValue( 'Dummy Text' );
		EditConflictPage.previewButton.click();

		assert(
			PreviewPage.previewView.waitForVisible(),
			'I see a preview page for my changes'
		);

		assert.strictEqual(
			PreviewPage.previewText.getText(),
			'Line1 Dummy Text',
			'text was saved correctly'
		);
	} );

	it( 'should show a diff page', function () {
		EditConflictPage.diffButton.click();

		assert(
			DiffPage.diffView.waitForVisible(),
			'I see a diff page for my changes'
		);

		assert(
			DiffPage.diffEmpty.isVisible(),
			'per default the diff is empty'
		);
	} );

	afterEach( function () {
		// provoke and dismiss reload warning
		browser.url( 'data:' );
		try {
			browser.alertAccept();
		} catch ( e ) {}
	} );

	after( function () {
		browser.deleteCookie();
	} );
} );
