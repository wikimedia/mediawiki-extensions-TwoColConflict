const Api = require( 'wdio-mediawiki/Api' ),
	Util = require( 'wdio-mediawiki/Util' );

class TestAccounts {
	// FIXME: Note that these cannot be lazy-initialized from within another browser.call

	get you() {
		return browser.call( async () => {
			return await Api.bot();
		} );
	}

	get other() {
		// Same thing as above: do not call from browser.call
		const adminBot = this.you,
			username = Util.getTestString( 'User-' ),
			password = Util.getTestString( 'pwd-' );

		return browser.call( async () => {
			await Api.createAccount( adminBot, username, password );
			return await Api.bot( username, password );
		} );
	}
}

module.exports = new TestAccounts();
