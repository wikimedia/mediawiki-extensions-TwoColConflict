const Page = require( 'wdio-mediawiki/Page' ),
	EditPage = require( '../../../../../tests/selenium/pageobjects/edit.page' ),
	Api = require( 'wdio-mediawiki/Api' ),
	Util = require( 'wdio-mediawiki/Util' ),
	MWBot = require( 'mwbot' );

class EditConflictPage extends Page {
	get conflictHeader() { return browser.element( '.mw-twocolconflict-split-header' ); }
	get conflictView() { return browser.element( '.mw-twocolconflict-split-view' ); }

	enforceSplitEditConflict() {
		browser.setCookie( {
			name: 'mw-twocolconflict-split-ui',
			value: '1'
		} );
	}

	createSimpleConflict( title, conflictUser, conflictUserPassword ) {
		browser.call( function () {
			return Api.edit(
				title,
				Util.getTestString( 'initialContent-' )
			);
		} );

		EditPage.openForEditing( title );

		browser.call( function () {
			let bot = new MWBot(),
				content = Util.getTestString( 'newContent2-' );

			return bot.loginGetEditToken( {
				apiUrl: `${browser.options.baseUrl}/api.php`,
				username: conflictUser,
				password: conflictUserPassword
			} ).then( function () {
				return bot.edit( title, content, `Changed content to "${content}"` );
			} );
		} );

		EditPage.content.setValue( Util.getTestString( 'newContent1-' ) );
		EditPage.save.click();
	}
}

module.exports = new EditConflictPage();
