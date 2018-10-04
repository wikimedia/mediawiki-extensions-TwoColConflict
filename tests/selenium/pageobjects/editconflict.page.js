const Page = require( 'wdio-mediawiki/Page' ),
	EditPage = require( '../../../../../tests/selenium/pageobjects/edit.page' ),
	BetaPreferencesPage = require( '../pageobjects/betapreferences.page' ),
	UserLoginPage = require( 'wdio-mediawiki/LoginPage' ),
	Api = require( 'wdio-mediawiki/Api' ),
	Util = require( 'wdio-mediawiki/Util' ),
	MWBot = require( 'mwbot' );

class EditConflictPage extends Page {
	get conflictHeader() { return browser.element( '.mw-twocolconflict-split-header' ); }
	get conflictView() { return browser.element( '.mw-twocolconflict-split-view' ); }

	get otherParagraph() { return browser.element( '.mw-twocolconflict-split-delete' ); }
	get yourParagraph() { return browser.element( '.mw-twocolconflict-split-add' ); }
	get unchangedParagraph() { return browser.element( '.mw-twocolconflict-split-copy' ); }
	get otherParagraphEditButton() { return browser.element( '.mw-twocolconflict-split-delete .mw-twocolconflict-split-edit-button' ); }
	get otherParagraphSaveButton() { return browser.element( '.mw-twocolconflict-split-delete .mw-twocolconflict-split-save-button' ); }
	get yourParagraphEditButton() { return browser.element( '.mw-twocolconflict-split-add .mw-twocolconflict-split-edit-button' ); }
	get yourParagraphSaveButton() { return browser.element( '.mw-twocolconflict-split-add .mw-twocolconflict-split-save-button' ); }
	get unchangedParagraphEditButton() { return browser.element( '.mw-twocolconflict-split-copy .mw-twocolconflict-split-edit-button' ); }
	get unchangedParagraphSaveButton() { return browser.element( '.mw-twocolconflict-split-copy .mw-twocolconflict-split-save-button' ); }
	get otherParagraphEditor() { return browser.element( '.mw-twocolconflict-split-delete .mw-twocolconflict-split-editor' ); }
	get yourParagraphEditor() { return browser.element( '.mw-twocolconflict-split-add .mw-twocolconflict-split-editor' ); }
	get unchangedParagraphEditor() { return browser.element( '.mw-twocolconflict-split-copy .mw-twocolconflict-split-editor' ); }
	get otherParagraphDiffText() { return browser.element( '.mw-twocolconflict-split-delete .mw-twocolconflict-split-difftext' ); }
	get yourParagraphDiffText() { return browser.element( '.mw-twocolconflict-split-add .mw-twocolconflict-split-difftext' ); }
	get unchangedParagraphDiffText() { return browser.element( '.mw-twocolconflict-split-copy .mw-twocolconflict-split-difftext' ); }
	get yourParagraphSelection() { return browser.element( '.mw-twocolconflict-split-selection div:nth-child(2) span' ); }

	get infoButton() { return browser.element( '.mw-twocolconflict-split-tour-help-button' ); }
	get tourDialog() { return browser.element( '.mw-twocolconflict-split-tour-intro-container' ); }
	get tourDialogCloseButton() { return browser.element( '.mw-twocolconflict-split-tour-intro-container a' ); }

	get tourDiffChangeButton() { return browser.element( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-still-button' ); }
	get tourSplitSelectionButton() { return browser.element( '.mw-twocolconflict-split-selection .mw-twocolconflict-split-tour-still-button' ); }
	get tourYourVersionHeaderButton() { return browser.element( '.mw-twocolconflict-split-your-version-header .mw-twocolconflict-split-tour-still-button' ); }

	get tourDiffChangePopup() { return browser.element( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-popup' ); }
	get tourDiffChangePopupCloseButton() { return browser.element( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-popup a' ); }

	enforceSplitEditConflict() {
		return browser.setCookie( {
			name: 'mw-twocolconflict-split-ui',
			value: '1'
		} );
	}

	toggleHelpDialogue( hide ) {
		browser.pause( 300 ); // wait for mw JS to load
		return browser.execute( function ( hide ) {
			return ( new mediaWiki.Api() ).saveOption(
				'userjs-twocolconflict-hide-help-dialogue',
				hide ? '1' : '0'
			);
		}, hide );
	}

	showsAnEditConflictWith( conflictUser, conflictUserPassword, hideHelpDialogue = true ) {
		UserLoginPage.loginAdmin();
		BetaPreferencesPage.enableTwoColConflictBetaFeature();
		this.toggleHelpDialogue( hideHelpDialogue );
		this.enforceSplitEditConflict();

		this.createSimpleConflict(
			Util.getTestString( 'conflict-title-' ),
			conflictUser,
			conflictUserPassword
		);

		this.infoButton.waitForVisible(); // JS for the tour is loaded
	}

	createSimpleConflict( title, conflictUser, conflictUserPassword ) {
		browser.call( function () {
			return Api.edit(
				title,
				"Line1\nLine2" // eslint-disable-line quotes
			);
		} );

		EditPage.openForEditing( title );

		browser.call( function () {
			let bot = new MWBot(),
				content = "Line1\nChangeA"; // eslint-disable-line quotes

			return bot.loginGetEditToken( {
				apiUrl: `${browser.options.baseUrl}/api.php`,
				username: conflictUser,
				password: conflictUserPassword
			} ).then( function () {
				return bot.edit( title, content, `Changed content to "${content}"` );
			} );
		} );

		EditPage.content.setValue( "Line1\nChangeB" ); // eslint-disable-line quotes
		EditPage.save.click();
	}
}

module.exports = new EditConflictPage();
