'use strict';

const EditConflictPage = require( '../pageobjects/editconflict.page' ),
	FinishedConflictPage = require( '../pageobjects/finishedconflict.page' ),
	TestAccounts = require( '../test_accounts' ),
	Util = require( 'wdio-mediawiki/Util' );

describe( 'TwoColConflict save and preview', () => {
	before( async () => {
		await EditConflictPage.prepareEditConflict();
	} );

	it( 'should save a resolved conflict successfully', async () => {
		await EditConflictPage.showSimpleConflict();

		await EditConflictPage.yourParagraphSelection.click();
		await EditConflictPage.getEditButton( 'your' ).click();
		await EditConflictPage.getEditor( 'your' ).setValue( 'Dummy Text' );
		await EditConflictPage.submitButton.click();

		await expect(
			await FinishedConflictPage.pageWikitext() ).toBe(
			'Line<span>1</span>\n\nDummy Text'
		);
	} );

	it( 'should save a resolved conflict successfully when another user edits a different section in the meantime', async () => {
		const title = Util.getTestString( 'conflict-title-' );

		// an initial conflict in a specific section
		await EditConflictPage.createConflict(
			'==A==\nSectionA\n==B==\nSectionB',
			'==A==\nSectionA\n==B==\nEdit1 <span lang="de">Other</span>',
			'==B==\nEdit2\\r <span lang="en">Your</span>',
			title,
			2
		);
		await EditConflictPage.waitForJS();

		// a user editing a different section while the initial conflict is still being resolved
		await EditConflictPage.apiEditPage(
			await TestAccounts.otherBot(),
			title,
			'==A==\nEdit3\n==B==\nEdit1 <span lang="de">Other</span>'
		);

		await EditConflictPage.yourParagraphSelection.click();
		await EditConflictPage.getEditButton( 'your' ).click();
		await EditConflictPage.submitButton.click();

		await expect(
			await FinishedConflictPage.pageWikitext() )
			.toBe( '==A==\nEdit3\n==B==\nEdit2\\r <span lang="en">Your</span>' );
	} );

	it( 'should trigger a new conflict when another user edits in the same lines in the meantime', async () => {
		const title = Util.getTestString( 'conflict-title-' );

		// an initial conflict
		await EditConflictPage.createConflict(
			'Line1\nLine2',
			'Line1\nChange A',
			'Line1\nChange B',
			title
		);
		await EditConflictPage.waitForJS();

		// a user editing in a line affected by the conflict above
		await EditConflictPage.apiEditPage( await TestAccounts.otherBot(), title, 'Line1\nThird Change C' );

		await EditConflictPage.yourParagraphSelection.click();
		await EditConflictPage.getEditButton( 'your' ).click();
		await EditConflictPage.getEditor( 'your' ).setValue( 'Merged AB' );
		await EditConflictPage.submitButton.click();

		await expect(
			EditConflictPage.conflictHeader ).toExist(
			{ message: 'there will be another edit conflict' }
		);
		await expect(
			EditConflictPage.conflictView ).toExist(
			{ message: 'there will be another edit conflict' }
		);

		await expect(
			await EditConflictPage.getDiffText( 'other' ).getText() ).toBe(
			'Third Change C',
			{ message: 'the other text will be the text of the third edit' }
		);

		await expect(
			await EditConflictPage.getDiffText( 'your' ).getText() ).toBe(
			'Merged AB',
			{ message: 'your text will be the result of the first merge' }
		);
	} );

	it( 'should show a correct preview page when changes are present', async () => {
		await EditConflictPage.showSimpleConflict();

		await EditConflictPage.yourParagraphSelection.click();
		await EditConflictPage.getEditButton( 'your' ).click();
		// make sure that pre-save transforms are rendered correctly
		await EditConflictPage.getEditor( 'your' ).setValue( 'Dummy Text [[title (topic)|]]' );
		await EditConflictPage.previewButton.click();

		await expect(
			EditConflictPage.previewView ).toBeDisplayed(
			{ message: 'I see a preview page for my changes' }
		);
		expect(
			await EditConflictPage.previewText.getText() ).toBe(
			'Line1\n\nDummy Text title'
		);
	} );

	it( 'should be possible to edit and preview the left ("other") side', async () => {
		await EditConflictPage.showSimpleConflict();

		await EditConflictPage.otherParagraphSelection.click();

		await EditConflictPage.getEditButton( 'other' ).click();
		await EditConflictPage.getEditor( 'other' ).setValue( 'Other, but improved' );
		await EditConflictPage.previewButton.click();

		await expect(
			EditConflictPage.previewView ).toBeDisplayed(
			{ message: 'The preview appears' }
		);

		expect(
			await EditConflictPage.previewText.getText() ).toBe(
			'Line1\n\nOther, but improved',
			{ message: 'My edit appears in the preview' }
		);

		expect(
			await EditConflictPage.getEditor( 'other' ).getValue() ).toBe(
			'Other, but improved',
			{ message: 'I can continue the edit I started' }
		);
	} );

	after( async () => {
		await browser.deleteCookies();
	} );
} );
