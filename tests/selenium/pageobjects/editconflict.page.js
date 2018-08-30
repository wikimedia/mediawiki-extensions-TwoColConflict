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

	get otherParagraphEditButton() { return browser.element( '.mw-twocolconflict-split-delete .mw-twocolconflict-split-edit-button' ); }
	get yourParagraphEditButton() { return browser.element( '.mw-twocolconflict-split-add .mw-twocolconflict-split-edit-button' ); }
	get unchangedParagraphEditButton() { return browser.element( '.mw-twocolconflict-split-copy .mw-twocolconflict-split-edit-button' ); }
	get yourParagraphSelection() { return browser.element( '.mw-twocolconflict-split-selection div:nth-child(2) span' ); }

	enforceSplitEditConflict() {
		browser.setCookie( {
			name: 'mw-twocolconflict-split-ui',
			value: '1'
		} );
	}

	showsAnEditConflictWith( conflictUser, conflictUserPassword ) {
		UserLoginPage.loginAdmin();
		BetaPreferencesPage.enableTwoColConflictBetaFeature();
		this.enforceSplitEditConflict();

		this.createSimpleConflict(
			Util.getTestString( 'conflict-title-' ),
			conflictUser,
			conflictUserPassword
		);
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
