'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	FinishedConflictPage = require( '../pageobjects/finishedconflict.page' ),
	TalkConflictPage = require( '../pageobjects/talkconflict.page' );

describe( 'TwoColConflict without JavaScript', function () {
	before( function () {
		EditConflictPage.prepareEditConflict();
		EditConflictPage.testNoJs();
	} );

	it( 'is showing the default version correctly', function () {
		EditConflictPage.createConflict(
			'A',
			'B',
			'C'
		);
		// wait for the nojs script to switch CSS visibility
		EditConflictPage.getEditor( 'your' ).waitForDisplayed();

		assert( EditConflictPage.conflictHeader.isDisplayed() );
		assert( EditConflictPage.conflictView.isDisplayed() );
		assert(
			EditConflictPage.yourParagraphRadio.isSelected() &&
			!EditConflictPage.otherParagraphRadio.isSelected(),
			'your side is selected by default'
		);
		assert(
			EditConflictPage.getEditor( 'your' ).isDisplayed() &&
			EditConflictPage.getEditor( 'other' ).isDisplayed(),
			'editors are visible right away'
		);
	} );

	it( 'is showing the talk page version correctly', function () {
		TalkConflictPage.createTalkPageConflict();

		assert( !TalkConflictPage.splitColumn.isExisting() );

		TalkConflictPage.orderSelector.waitForDisplayed();
		assert( TalkConflictPage.keepAfterButton.isSelected() );

		assert( EditConflictPage.getParagraph( 'other' ) );
		assert( EditConflictPage.getParagraph( 'your' ) );
		assert( EditConflictPage.getParagraph( 'copy' ) );
	} );

	it( 'handles order selection on the talk page version correctly', function () {
		TalkConflictPage.createTalkPageConflict();
		TalkConflictPage.orderSelector.waitForDisplayed();

		TalkConflictPage.moveBeforeButton.click();
		EditConflictPage.submitButton.click();

		assert.strictEqual(
			FinishedConflictPage.pageWikitext,
			'Line1\nLine2\nLine3\nComment <span lang="en">B</span>\nComment <span lang="de">A</span>'
		);
	} );

	after( function () {
		browser.deleteCookies();
	} );
} );
