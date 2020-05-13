var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	FinishedConflictPage = require( '../pageobjects/finishedconflict.page' ),
	TalkConflictPage = require( '../pageobjects/talkconflict.page' );

describe( 'TwoColConflict', function () {
	before( function () {
		EditConflictPage.prepareEditConflict();
	} );

	it( 'shows the talk page screen correctly', function () {
		TalkConflictPage.createTalkPageConflict();

		assert( !TalkConflictPage.splitColumn.isExisting() );

		assert( EditConflictPage.getParagraph( 'other' ) );
		assert( EditConflictPage.getParagraph( 'your' ) );
		assert( EditConflictPage.getParagraph( 'copy' ) );

		// Only "your" block is editable
		assert( EditConflictPage.getEditButton( 'your' ).isExisting() );
		assert( !EditConflictPage.getEditButton( 'other' ).isExisting() );
		assert( !EditConflictPage.getEditButton( 'copy' ).isExisting() );

		assert( TalkConflictPage.isOtherBlockFirst() );
	} );

	it( 'swaps blocks when switch button is clicked', function () {
		TalkConflictPage.createTalkPageConflict();
		EditConflictPage.waitForJS();

		TalkConflictPage.swapButton.click();

		assert( TalkConflictPage.isYourBlockFirst() );
	} );

	it( 'shows correct preview after edit', function () {
		TalkConflictPage.createTalkPageConflict();
		EditConflictPage.getEditButton( 'your' ).waitForEnabled();

		EditConflictPage.waitForJS();
		TalkConflictPage.editMyComment( 'Comment edited' );
		EditConflictPage.previewButton.click();

		assert( EditConflictPage.previewView.waitForDisplayed() );

		assert.strictEqual(
			EditConflictPage.previewText.getText(),
			'Line1 Line2 Line3 Comment A Comment edited'
		);
	} );

	it( 'shows correct preview swapped', function () {
		TalkConflictPage.createTalkPageConflict();
		EditConflictPage.waitForJS();

		TalkConflictPage.swapButton.click();

		EditConflictPage.previewButton.click();

		assert( EditConflictPage.previewView.waitForDisplayed() );

		assert.strictEqual(
			EditConflictPage.previewText.getText(),
			'Line1 Line2 Line3 Comment B Comment A'
		);
	} );

	it( 'stores correct merge after edit', function () {
		TalkConflictPage.createTalkPageConflict();
		EditConflictPage.getEditButton( 'your' ).waitForEnabled();

		EditConflictPage.waitForJS();
		TalkConflictPage.editMyComment( 'Comment edited' );
		EditConflictPage.submitButton.click();

		assert.strictEqual(
			FinishedConflictPage.pageText.getText(),
			'Line1 Line2 Line3 Comment A Comment edited'
		);
	} );

	it( 'stores correct merge swapped', function () {
		TalkConflictPage.createTalkPageConflict();
		EditConflictPage.waitForJS();

		TalkConflictPage.swapButton.click();

		EditConflictPage.submitButton.click();

		assert.strictEqual(
			FinishedConflictPage.pageText.getText(),
			'Line1 Line2 Line3 Comment B Comment A'
		);
	} );

	// TODO: test for double-conflict, all text should be restored even if edited.

	after( function () {
		browser.deleteAllCookies();
	} );
} );
