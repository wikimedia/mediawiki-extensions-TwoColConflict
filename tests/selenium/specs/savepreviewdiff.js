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

	it( 'should resolve the conflict successfully', function () {
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );

		EditConflictPage.submitButton.click();

		assert.strictEqual(
			FinishedConflictPage.pageText.getText(),
			'Line1 Change A',
			'text was saved correctly'
		);
	} );

	it( 'should resolve the ongoing conflict successfully when another user edits a different section in the meantime', function () {
		const title = Util.getTestString( 'conflict-title-' );

		// an initial conflict in a specific section
		EditConflictPage.createConflict(
			conflictUser,
			conflictUserPassword,
			'==A==\nSectionA\n==B==\nSectionB',
			'==A==\nSectionA\n==B==\nEdit1 <span lang="de">Other</span>',
			'==B==\nEdit2\\r <span lang="en">Your</span>',
			title,
			2
		);

		// a user editing a different section while the initial conflict is still being resolved
		EditConflictPage.editPage(
			title,
			'==A==\nEdit3\n==B==\nEdit1 <span lang="de">Other</span>',
			conflictUser,
			conflictUserPassword
		);

		EditConflictPage.yourParagraphSelection.click();
		EditConflictPage.getEditButton( 'your' ).click();
		EditConflictPage.submitButton.click();

		assert.strictEqual(
			FinishedConflictPage.pageText.getText(),
			'A[edit]\nEdit3\nB[edit]\nEdit2\\r Your',
			'text was saved correctly'
		);
	} );

	it( 'should trigger a new conflict when another user edits in the same lines in the meantime', function () {
		const title = Util.getTestString( 'conflict-title-' );

		// an initial conflict
		EditConflictPage.createConflict(
			conflictUser,
			conflictUserPassword,
			'Line1\nLine2',
			'Line1\nChange A',
			'Line1\nChange B',
			title
		);

		// a user editing in a line affected by the conflict above
		EditConflictPage.editPage(
			title,
			'Line1\nThird Change C',
			conflictUser,
			conflictUserPassword
		);

		EditConflictPage.yourParagraphSelection.click();
		EditConflictPage.getEditButton( 'your' ).click();
		EditConflictPage.getEditor( 'your' ).setValue( 'Merged AB' );
		EditConflictPage.submitButton.click();

		assert(
			EditConflictPage.conflictHeader.isExisting() && EditConflictPage.conflictView.isExisting(),
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

	it( 'should resolve the conflict successfully when unsaved edits in selected paragraphs are present', function () {
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );

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
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );

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
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );

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

	it( 'should show a correct preview page when using pre-save transforms', function () {
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );

		EditConflictPage.yourParagraphSelection.click();
		EditConflictPage.getEditButton( 'your' ).click();
		EditConflictPage.getEditor( 'your' ).setValue( 'Dummy Text [[title (topic)|]]' );
		EditConflictPage.previewButton.click();

		assert(
			EditConflictPage.previewView.waitForVisible(),
			'I see a preview page for my changes'
		);

		assert.strictEqual(
			EditConflictPage.previewText.getText(),
			'Line1 Dummy Text title',
			'text was saved correctly'
		);
	} );

	it( 'should be possible to edit and preview the left ("other") side', function () {
		EditConflictPage.showSimpleConflict( conflictUser, conflictUserPassword );

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

	it( 'editor should not decode html entities', function () {
		EditConflictPage.createConflict(
			conflictUser,
			conflictUserPassword,
			'α\n&beta;',
			'α\n&gamma; <span lang="de">A</span>',
			'α\n&gamma; <span lang="en">B</span>'
		);

		EditConflictPage.getEditButton( 'other' ).click();

		assert.strictEqual(
			EditConflictPage.getEditor().getValue(),
			'α\n',
			'unchanged text editor did not decode html entities'
		);

		assert.strictEqual(
			EditConflictPage.getEditor( 'other' ).getValue(),
			'&gamma; <span lang="de">A</span>\n',
			'selectable text editor did not decode html entities'
		);
	} );

	after( function () {
		browser.deleteCookie();
	} );
} );
