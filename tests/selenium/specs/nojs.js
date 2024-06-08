'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	FinishedConflictPage = require( '../pageobjects/finishedconflict.page' ),
	TalkConflictPage = require( '../pageobjects/talkconflict.page' );

describe( 'TwoColConflict without JavaScript', () => {
	before( async () => {
		await EditConflictPage.prepareEditConflict();
		await EditConflictPage.testNoJs();
	} );

	it( 'is showing the default version correctly', async () => {
		await EditConflictPage.createConflict(
			'A',
			'B',
			'C'
		);
		// wait for the nojs script to switch CSS visibility
		await EditConflictPage.getEditor( 'your' ).waitForDisplayed();

		assert( await EditConflictPage.conflictHeader.isDisplayed() );
		assert( await EditConflictPage.conflictView.isDisplayed() );
		assert(
			await EditConflictPage.yourParagraphRadio.isSelected() &&
			!( await EditConflictPage.otherParagraphRadio.isSelected() ),
			'your side is selected by default'
		);
		assert(
			await EditConflictPage.getEditor( 'your' ).isDisplayed() &&
			await EditConflictPage.getEditor( 'other' ).isDisplayed(),
			'editors are visible right away'
		);
	} );

	it( 'is showing the talk page version correctly', async () => {
		await TalkConflictPage.createTalkPageConflict();

		assert( !( await TalkConflictPage.splitColumn.isExisting() ) );

		await TalkConflictPage.orderSelector.waitForDisplayed();
		assert( await TalkConflictPage.keepAfterButton.isSelected() );

		assert( await EditConflictPage.getParagraph( 'other' ) );
		assert( await EditConflictPage.getParagraph( 'your' ) );
		assert( await EditConflictPage.getParagraph( 'copy' ) );
	} );

	it( 'handles order selection on the talk page version correctly', async () => {
		await TalkConflictPage.createTalkPageConflict();
		await TalkConflictPage.orderSelector.waitForDisplayed();

		await TalkConflictPage.moveBeforeButton.click();
		await EditConflictPage.submitButton.click();

		assert.strictEqual(
			await FinishedConflictPage.pageWikitext(),
			'Line1\nLine2\nLine3\nComment <span lang="en">B</span>\nComment <span lang="de">A</span>'
		);
	} );

	after( async () => {
		await browser.deleteCookies();
	} );
} );
