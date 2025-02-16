'use strict';

const EditConflictPage = require( '../pageobjects/editconflict.page' ),
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

		await expect( EditConflictPage.conflictHeader ).toBeDisplayed();
		await expect( EditConflictPage.conflictView ).toBeDisplayed();
		await expect(
			EditConflictPage.yourParagraphRadio ).toBeSelected(
			{ message: 'your side is selected by default' }
		);
		await expect(
			EditConflictPage.otherParagraphRadio ).not.toBeSelected(
			{ message: 'other side is not selected by default' }
		);
		await expect(
			EditConflictPage.getEditor( 'your' ) ).toBeDisplayed(
			{ message: '"your" editor is visible right away' }
		);
		await expect(
			EditConflictPage.getEditor( 'other' ) ).toBeDisplayed(
			{ message: '"other" editor is visible right away' }
		);
	} );

	it( 'is showing the talk page version correctly', async () => {
		await TalkConflictPage.createTalkPageConflict();

		await expect( TalkConflictPage.splitColumn ).not.toExist();

		await TalkConflictPage.orderSelector.waitForDisplayed();
		await expect( TalkConflictPage.keepAfterButton ).toBeSelected();

		await expect( await EditConflictPage.getParagraph( 'other' ) ).toExist();
		await expect( await EditConflictPage.getParagraph( 'your' ) ).toExist();
		await expect( await EditConflictPage.getParagraph( 'copy' ) ).toExist();
	} );

	it( 'handles order selection on the talk page version correctly', async () => {
		await TalkConflictPage.createTalkPageConflict();
		await TalkConflictPage.orderSelector.waitForDisplayed();

		await TalkConflictPage.moveBeforeButton.click();
		await EditConflictPage.submitButton.click();

		await expect(
			await FinishedConflictPage.pageWikitext() ).toBe(
			'Line1\nLine2\nLine3\nComment <span lang="en">B</span>\nComment <span lang="de">A</span>'
		);
	} );

	after( async () => {
		await browser.deleteCookies();
	} );
} );
