var assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' );

describe( 'TwoColConflict', function () {
	before( function () {
		EditConflictPage.prepareEditConflict();
	} );

	beforeEach( function () {
		EditConflictPage.showBigConflict();
	} );

	it( 'collapses long unchanged paragraphs', function () {
		assert(
			EditConflictPage.fadeOverlay.isVisible(),
			'an overlay fades the collapsed text'
		);
		assert(
			EditConflictPage.collapsedParagraph.isVisible(),
			'the collapsed paragraph text is visible'
		);
		assert(
			!EditConflictPage.expandedParagraph.isVisible(),
			'the expanded paragraph text is hidden'
		);
		assert(
			EditConflictPage.expandButton.isVisible(),
			'the expand button is visible'
		);
		assert(
			!EditConflictPage.collapseButton.isVisible(),
			'the collapse button is hidden'
		);
	} );

	it( 'can expand collapsed paragraphs', function () {
		EditConflictPage.expandButton.click();

		assert(
			!EditConflictPage.fadeOverlay.isVisible(),
			'no overlay fades the collapsed text'
		);
		assert(
			EditConflictPage.expandedParagraph.isVisible(),
			'the expanded paragraph text is visible'
		);
		assert(
			!EditConflictPage.collapsedParagraph.isVisible(),
			'the collapsed paragraph text is hidden'
		);
		assert(
			!EditConflictPage.expandButton.isVisible(),
			'the expand button is hidden'
		);
		assert(
			EditConflictPage.collapseButton.isVisible(),
			'the collapse button is visible'
		);
	} );

	it( 'expands edited collapsed paragraphs and allows re-collapsing', function () {
		const unchangedParagraphNewText = 'Dummy Text';

		EditConflictPage.getEditButton( 'unchanged' ).click();
		EditConflictPage.getEditor( 'unchanged' ).setValue( unchangedParagraphNewText );
		EditConflictPage.getSaveButton( 'unchanged' ).click();

		assert(
			!EditConflictPage.fadeOverlay.isVisible(),
			'no overlay fades the collapsed text'
		);
		assert(
			EditConflictPage.expandedParagraph.isVisible(),
			'the expanded paragraph text is visible'
		);
		assert(
			!EditConflictPage.collapsedParagraph.isVisible(),
			'the collapsed paragraph text is hidden'
		);
		assert(
			!EditConflictPage.expandButton.isVisible(),
			'the expand button is hidden'
		);
		assert(
			EditConflictPage.collapseButton.isVisible(),
			'the collapse button is visible'
		);
		assert.strictEqual(
			EditConflictPage.getDiffText( 'unchanged' ).getText(),
			unchangedParagraphNewText,
			'unchanged text diff was edited'
		);
	} );

	it( 'resets collapsing when changes are discarded', function () {
		EditConflictPage.getEditButton( 'unchanged' ).click();
		EditConflictPage.getEditor( 'unchanged' ).setValue( 'Dummy Text' );
		EditConflictPage.getSaveButton( 'unchanged' ).click();

		EditConflictPage.getEditButton( 'unchanged' ).click();
		EditConflictPage.getResetButton( 'unchanged' ).click();

		EditConflictPage.resetConfirmationPopup.waitForVisible( 1000 );
		EditConflictPage.resetConfirmationButton.click();
		EditConflictPage.resetConfirmationButton.waitForVisible( 1000, true );

		assert(
			!EditConflictPage.fadeOverlay.isVisible(),
			'no overlay fades the collapsed text'
		);
		assert(
			EditConflictPage.expandedParagraph.isVisible(),
			'the expanded paragraph text is visible'
		);
		assert(
			!EditConflictPage.collapsedParagraph.isVisible(),
			'the collapsed paragraph text is hidden'
		);
		assert(
			!EditConflictPage.expandButton.isVisible(),
			'the expand button is hidden'
		);
		assert(
			EditConflictPage.collapseButton.isVisible(),
			'the collapse button is visible'
		);
	} );

	after( function () {
		browser.deleteCookie();
	} );
} );
