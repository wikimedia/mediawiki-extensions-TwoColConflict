'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	FinishedConflictPage = require( '../pageobjects/finishedconflict.page' ),
	TestAccounts = require( '../test_accounts' ),
	Util = require( 'wdio-mediawiki/Util' );

describe( 'TwoColConflict save and preview', function () {
	before( function () {
		EditConflictPage.prepareEditConflict();
	} );

	it( 'should save a resolved conflict successfully', function () {
		EditConflictPage.showSimpleConflict();

		EditConflictPage.yourParagraphSelection.click();
		EditConflictPage.getEditButton( 'your' ).click();
		EditConflictPage.getEditor( 'your' ).setValue( 'Dummy Text' );
		EditConflictPage.submitButton.click();

		assert.strictEqual(
			FinishedConflictPage.pageWikitext,
			'Line<span>1</span>\n\nDummy Text'
		);
	} );

	it( 'should save a resolved conflict successfully when another user edits a different section in the meantime', function () {
		const title = Util.getTestString( 'conflict-title-' );

		// an initial conflict in a specific section
		EditConflictPage.createConflict(
			'==A==\nSectionA\n==B==\nSectionB',
			'==A==\nSectionA\n==B==\nEdit1 <span lang="de">Other</span>',
			'==B==\nEdit2\\r <span lang="en">Your</span>',
			title,
			2
		);
		EditConflictPage.waitForJS();

		// a user editing a different section while the initial conflict is still being resolved
		EditConflictPage.editPage(
			TestAccounts.other,
			title,
			'==A==\nEdit3\n==B==\nEdit1 <span lang="de">Other</span>'
		);

		EditConflictPage.yourParagraphSelection.click();
		EditConflictPage.getEditButton( 'your' ).click();
		EditConflictPage.submitButton.click();

		assert.strictEqual(
			FinishedConflictPage.pageWikitext,
			'==A==\nEdit3\n==B==\nEdit2\\r <span lang="en">Your</span>'
		);
	} );

	it( 'should trigger a new conflict when another user edits in the same lines in the meantime', function () {
		const title = Util.getTestString( 'conflict-title-' );

		// an initial conflict
		EditConflictPage.createConflict(
			'Line1\nLine2',
			'Line1\nChange A',
			'Line1\nChange B',
			title
		);
		EditConflictPage.waitForJS();

		// a user editing in a line affected by the conflict above
		EditConflictPage.editPage(
			TestAccounts.other,
			title,
			'Line1\nThird Change C'
		);

		EditConflictPage.yourParagraphSelection.click();
		EditConflictPage.getEditButton( 'your' ).click();
		EditConflictPage.getEditor( 'your' ).setValue( 'Merged AB' );
		EditConflictPage.submitButton.click();

		assert(
			EditConflictPage.conflictHeader.isExisting() &&
			EditConflictPage.conflictView.isExisting(),
			'there will be another edit conflict'
		);
		assert.strictEqual(
			EditConflictPage.getDiffText( 'other' ).getText(),
			'Third Change C',
			'the other text will be the text of the third edit'

		);
		assert.strictEqual(
			EditConflictPage.getDiffText( 'your' ).getText(),
			'Merged AB',
			'your text will be the result of the first merge'
		);
	} );

	it( 'should show a correct preview page when changes are present', function () {
		EditConflictPage.showSimpleConflict();

		EditConflictPage.yourParagraphSelection.click();
		EditConflictPage.getEditButton( 'your' ).click();
		// make sure that pre-save transforms are rendered correctly
		EditConflictPage.getEditor( 'your' ).setValue( 'Dummy Text [[title (topic)|]]' );
		EditConflictPage.previewButton.click();

		assert(
			EditConflictPage.previewView.waitForDisplayed(),
			'I see a preview page for my changes'
		);

		assert.strictEqual(
			EditConflictPage.previewText.getText(),
			'Line1\n\nDummy Text title'
		);
	} );

	it( 'should be possible to edit and preview the left ("other") side', function () {
		EditConflictPage.showSimpleConflict();

		EditConflictPage.otherParagraphSelection.click();

		EditConflictPage.getEditButton( 'other' ).click();
		EditConflictPage.getEditor( 'other' ).setValue( 'Other, but improved' );
		EditConflictPage.previewButton.click();

		assert(
			EditConflictPage.previewView.waitForDisplayed(),
			'The preview appears'
		);

		assert.strictEqual(
			EditConflictPage.previewText.getText(),
			'Line1\n\nOther, but improved',
			'My edit appears in the preview'
		);

		assert.strictEqual(
			EditConflictPage.getEditor( 'other' ).getValue(),
			'Other, but improved',
			'I can continue the edit I started'
		);
	} );

	after( function () {
		browser.deleteCookies();
	} );
} );
