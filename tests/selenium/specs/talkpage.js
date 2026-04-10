import EditConflictPage from '../pageobjects/editconflict.page.js';
import FinishedConflictPage from '../pageobjects/finishedconflict.page.js';
import TalkConflictPage from '../pageobjects/talkconflict.page.js';
import { getTestString } from 'wdio-mediawiki/Util';

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
			expect( await TalkConflictPage.splitColumn.isExisting() ).toBe( false );

			await expect( await EditConflictPage.getParagraph( 'other' ) ).toExist();
			await expect( await EditConflictPage.getParagraph( 'your' ) ).toExist();
			await expect( await EditConflictPage.getParagraph( 'copy' ) ).toExist();

			// Only "your" block is editable
			await expect( await EditConflictPage.getEditButton( 'your' ) ).toExist();
			expect( await EditConflictPage.getEditButton( 'other' ).isExisting() ).toBe( false );
			expect( await EditConflictPage.getEditButton( 'copy' ).isExisting() ).toBe( false );

			await expect( await TalkConflictPage.isOtherBlockFirst() ).toBeTruthy();
		} );

		it( 'swaps blocks when switch button is clicked', async () => {
			await TalkConflictPage.swapButton.click();

			await expect( await TalkConflictPage.isYourBlockFirst() ).toBeTruthy();
		} );

		it( 'shows correct preview when swapped', async () => {
			await EditConflictPage.previewButton.click();

			await expect( EditConflictPage.previewView ).toBeDisplayed();

			await expect(
				await EditConflictPage.previewText.getText() ).toBe(
				'Line1 Line2 Line3 Comment B Comment A'
			);
		} );

		it( 'stores correct merge when swapped and edited', async () => {
			await TalkConflictPage.editMyComment( 'Comment B edited' );

			await EditConflictPage.submitButton.click();

			await expect(
				await FinishedConflictPage.pageWikitext() ).toBe(
				'Line1\nLine2\nLine3\nComment B edited\nComment <span lang="de">A</span>'
			);
		} );
	} );

	it( 'shows the talk page screen on conflicts that also add new lines', async () => {
		await EditConflictPage.createConflict(
			'Line1\n\nLine2',
			'Line1\nComment <span lang="de">A</span>\nLine2',
			'Line1\nComment <span lang="en">B</span>\n\nLine2',
			getTestString( 'Talk:Test-conflict-' )
		);
		await TalkConflictPage.talkRow.waitForDisplayed();

		expect( await TalkConflictPage.splitColumn.isExisting() ).toBe( false );
	} );

	// TODO: test for double-conflict, all text should be restored even if edited.

	after( async () => {
		await browser.deleteAllCookies();
	} );
} );
