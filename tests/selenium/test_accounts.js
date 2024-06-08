'use strict';

const Api = require( 'wdio-mediawiki/Api' ),
	UserLoginPage = require( 'wdio-mediawiki/LoginPage' ),
	Util = require( 'wdio-mediawiki/Util' );

class TestAccounts {
	// FIXME: Note that these cannot be lazy-initialized from within another browser.call

	get adminBot() {
		return browser.call( async () => await Api.bot() );
	}

	async createUserAccount() {
		const credentials = {
			username: Util.getTestString( 'User-' ),
			password: Util.getTestString( 'pwd-' )
		};
		await Api.createAccount( await this.adminBot, credentials.username, credentials.password );
		return credentials;
	}

	async loginAsUser() {
		const credentials = await this.createUserAccount();
		await UserLoginPage.login( credentials.username, credentials.password );
	}

	async otherBot() {
		const credentials = await this.createUserAccount();
		return await browser.call( async () => await Api.bot( credentials.username, credentials.password ) );
	}
}

module.exports = new TestAccounts();
