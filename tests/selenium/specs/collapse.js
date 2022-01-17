'use strict';

const EditConflictPage = require( '../pageobjects/editconflict.page' );

describe( 'TwoColConflict collapse button', function () {
	before( function () {
		EditConflictPage.prepareEditConflict();
	} );

	beforeEach( function () {
		EditConflictPage.showBigConflict();
	} );

	it( 'collapses and expands long unchanged paragraphs', function () {
		EditConflictPage.assertUnchangedIsCollapsed();
		EditConflictPage.expandButton.click();
		EditConflictPage.assertUnchangedIsExpanded();
		EditConflictPage.collapseButton.click();
		EditConflictPage.assertUnchangedIsCollapsed();
	} );

	it( 'expands collapsed paragraphs after editing or aborting edits', function () {
		const unchangedParagraphNewText = 'Dummy Text';

		EditConflictPage.getEditButton( 'unchanged' ).click();
		EditConflictPage.getEditor( 'unchanged' ).setValue( unchangedParagraphNewText );
		EditConflictPage.getResetButton( 'unchanged' ).click();

		EditConflictPage.resetConfirmationPopup.waitForDisplayed( { timeout: 2000 } );
		EditConflictPage.resetConfirmationButton.click();
		EditConflictPage.resetConfirmationButton.waitForDisplayed( {
			timeout: 2000,
			reverse: true
		} );

		EditConflictPage.assertUnchangedIsExpanded();

		EditConflictPage.collapseButton.click();
		EditConflictPage.assertUnchangedIsCollapsed();

		EditConflictPage.getEditButton( 'unchanged' ).click();
		EditConflictPage.getEditor( 'unchanged' ).setValue( unchangedParagraphNewText );
		EditConflictPage.getSaveButton( 'unchanged' ).click();

		EditConflictPage.assertUnchangedIsExpanded();
	} );

	after( function () {
		browser.deleteCookies();
	} );
} );
