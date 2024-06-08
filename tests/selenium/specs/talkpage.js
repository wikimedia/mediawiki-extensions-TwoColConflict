'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	FinishedConflictPage = require( '../pageobjects/finishedconflict.page' ),
	TalkConflictPage = require( '../pageobjects/talkconflict.page' ),
	Util = require( 'wdio-mediawiki/Util' );

describe( 'TwoColConflict', () => {
	before( async () => {
		await EditConflictPage.prepareEditConflict();
	} );

	describe( 'on talk page conflicts', () => {
		before( async () => {
			await TalkConflictPage.createTalkPageConflict();
			await EditConflictPage.waitForJS();
		} );

		it( 'shows the talk page screen correctly', async () => {
			assert( !( await TalkConflictPage.splitColumn.isExisting() ) );

			assert( await EditConflictPage.getParagraph( 'other' ) );
			assert( await EditConflictPage.getParagraph( 'your' ) );
			assert( await EditConflictPage.getParagraph( 'copy' ) );

			// Only "your" block is editable
			assert( await EditConflictPage.getEditButton( 'your' ).isExisting() );
			assert( !( await EditConflictPage.getEditButton( 'other' ).isExisting() ) );
			assert( !( await EditConflictPage.getEditButton( 'copy' ).isExisting() ) );

			assert( await TalkConflictPage.isOtherBlockFirst() );
		} );

		it( 'swaps blocks when switch button is clicked', async () => {
			await TalkConflictPage.swapButton.click();

			assert( await TalkConflictPage.isYourBlockFirst() );
		} );

		it( 'shows correct preview when swapped', async () => {
			await EditConflictPage.previewButton.click();

			assert( await EditConflictPage.previewView.waitForDisplayed() );

			assert.strictEqual(
				await EditConflictPage.previewText.getText(),
				'Line1 Line2 Line3 Comment B Comment A'
			);
		} );

		it( 'stores correct merge when swapped and edited', async () => {
			await TalkConflictPage.editMyComment( 'Comment B edited' );

			await EditConflictPage.submitButton.click();

			assert.strictEqual(
				await FinishedConflictPage.pageWikitext(),
				'Line1\nLine2\nLine3\nComment B edited\nComment <span lang="de">A</span>'
			);
		} );
	} );

	it( 'shows the talk page screen on conflicts that also add new lines', async () => {
		await EditConflictPage.createConflict(
			'Line1\n\nLine2',
			'Line1\nComment <span lang="de">A</span>\nLine2',
			'Line1\nComment <span lang="en">B</span>\n\nLine2',
			Util.getTestString( 'Talk:Test-conflict-' )
		);
		await TalkConflictPage.talkRow.waitForDisplayed();

		assert( !( await TalkConflictPage.splitColumn.isExisting() ) );
	} );

	// TODO: test for double-conflict, all text should be restored even if edited.

	after( async () => {
		await browser.deleteAllCookies();
	} );
} );
