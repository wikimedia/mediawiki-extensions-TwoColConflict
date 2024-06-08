'use strict';

const assert = require( 'assert' ),
	EditConflictPage = require( '../pageobjects/editconflict.page' ),
	PreferencesPage = require( '../pageobjects/preferences.page' );

describe( 'TwoColConflict', () => {
	before( async () => {
		await EditConflictPage.prepareEditConflict();
	} );

	it( 'labels change according to selected column', async () => {
		await EditConflictPage.showSimpleConflict();

		const initialText = await EditConflictPage.selectionLabel.getText();
		assert.strictEqual( initialText, 'Please select a version' );

		await EditConflictPage.yourParagraphSelection.click();

		const yourSelectionText = await EditConflictPage.selectionLabel.getText();
		assert.strictEqual( yourSelectionText, 'Your version',
			'Your side is selected when you click the row\'s radio button'
		);

		await EditConflictPage.otherParagraphAllSelection.click();

		const otherSelectionText = await EditConflictPage.selectionLabel.getText();
		assert.strictEqual( otherSelectionText, 'Other version',
			'The other side is selected when you click the other side\'s select all button'
		);
	} );

	it( 'editor should not decode html entities', async () => {
		await EditConflictPage.createConflict(
			'α\n&beta;',
			'α\n&gamma; <span lang="de">A</span>',
			'α\n&gamma; <span lang="en">B</span>'
		);
		await EditConflictPage.waitForJS();

		await EditConflictPage.otherParagraphSelection.click();

		await EditConflictPage.getEditButton( 'other' ).click();

		assert.strictEqual(
			await EditConflictPage.getEditor().getValue(),
			'α\n',
			'unchanged text editor did not decode html entities'
		);

		assert.strictEqual(
			await EditConflictPage.getEditor( 'other' ).getValue(),
			'&gamma; <span lang="de">A</span>\n',
			'selectable text editor did not decode html entities'
		);
	} );

	it( 'shows a dismissible hint on the core edit conflict interface', async function () {
		if ( browser.config.baseUrl.includes( 'beta.wmflabs.org' ) ) {
			// FIXME This test does not work on the beta cluster. No idea why.
			this.skip( 'Skipping, very flaky on the beta cluster' );
		}
		try {
			await PreferencesPage.openBetaFeaturesPreferences();
		} catch ( e ) {
			this.skip( 'Failed to load beta preferences.' );
		}
		if ( await PreferencesPage.hasBetaFeatureSetting() ) {
			this.skip( 'Is run in beta feature mode.' );
		}
		await PreferencesPage.shouldUseTwoColConflict( false );
		await PreferencesPage.resetCoreHintVisibility();
		await EditConflictPage.createConflict(
			'A',
			'B',
			'C'
		);

		assert(
			await EditConflictPage.coreUiHint.isDisplayed(),
			'the core conflict UI shows a hint to enable the new interface'
		);

		await EditConflictPage.waitForJS();
		await EditConflictPage.coreUiHintCloseButton.click();

		assert(
			!( await EditConflictPage.coreUiHint.isDisplayed() ),
			'the hint on the core conflict is hidden'
		);

		await EditConflictPage.createConflict(
			'A',
			'B',
			'C'
		);

		assert(
			!( await EditConflictPage.coreUiHint.isDisplayed() ),
			'the hint on the core conflict is hidden on the next conflict'
		);
	} );

	after( async () => {
		await browser.deleteCookies();
	} );
} );
