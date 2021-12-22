'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	PreferencesPage = require( '../pageobjects/preferences.page' );

describe( 'TwoColConflict', function () {
	before( function () {
		EditConflictPage.prepareEditConflict();
	} );

	it( 'labels change according to selected column', function () {
		EditConflictPage.showSimpleConflict();

		const initialText = EditConflictPage.selectionLabel.getText();

		EditConflictPage.yourParagraphSelection.click();

		const yourSelectionText = EditConflictPage.selectionLabel.getText();

		assert(
			initialText !== yourSelectionText,
			'Your side is selected when you click the row\'s radio button'
		);

		EditConflictPage.otherParagraphAllSelection.click();

		const otherSelectionText = EditConflictPage.selectionLabel.getText();

		assert(
			yourSelectionText !== otherSelectionText && initialText !== otherSelectionText,
			'The other side is selected when you click the other side\'s select all button'
		);
	} );

	it( 'editor should not decode html entities', function () {
		EditConflictPage.createConflict(
			'α\n&beta;',
			'α\n&gamma; <span lang="de">A</span>',
			'α\n&gamma; <span lang="en">B</span>'
		);
		EditConflictPage.waitForJS();

		EditConflictPage.otherParagraphSelection.click();

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

	it( 'shows a dismissible hint on the core edit conflict interface', function () {
		try {
			PreferencesPage.openBetaFeaturesPreferences();
		} catch ( e ) {
			this.skip( 'Failed to load beta preferences.' );
		}
		if ( PreferencesPage.hasBetaFeatureSetting() ) {
			this.skip( 'Is run in beta feature mode.' );
		}
		PreferencesPage.shouldUseTwoColConflict( false );
		PreferencesPage.resetCoreHintVisibility();
		EditConflictPage.createConflict(
			'A',
			'B',
			'C'
		);

		assert(
			EditConflictPage.coreUiHint.isDisplayed(),
			'the core conflict UI shows a hint to enable the new interface'
		);

		EditConflictPage.waitForJS();
		EditConflictPage.coreUiHintCloseButton.click();

		assert(
			!EditConflictPage.coreUiHint.isDisplayed(),
			'the hint on the core conflict is hidden'
		);

		EditConflictPage.createConflict(
			'A',
			'B',
			'C'
		);

		assert(
			!EditConflictPage.coreUiHint.isDisplayed(),
			'the hint on the core conflict is hidden on the next conflict'
		);
	} );

	after( function () {
		browser.deleteCookies();
	} );
} );
