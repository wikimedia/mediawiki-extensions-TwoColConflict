const Api = require( 'wdio-mediawiki/Api' ),
	Util = require( 'wdio-mediawiki/Util' );

class TestAccounts {
	get you() {
		return {
			username: browser.options.username,
			password: browser.options.password
		};
	}

	get other() {
		const username = Util.getTestString( 'User-' ),
			password = Util.getTestString( 'pwd-' );

		browser.call( function () {
			return Api.createAccount( username, password );
		} );

		return {
			username: username,
			password: password
		};
	}
}

module.exports = new TestAccounts();
