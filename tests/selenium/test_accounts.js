import { createApiClient } from 'wdio-mediawiki/Api';
import LoginPage from 'wdio-mediawiki/LoginPage';
import { getTestString } from 'wdio-mediawiki/Util';

class TestAccounts {
	async createUserAccount() {
		const credentials = {
			username: getTestString( 'User-' ),
			password: getTestString( 'pwd-' )
		};
		const apiClient = await createApiClient();
		await apiClient.createAccount( credentials.username, credentials.password );
		return credentials;
	}

	async loginAsUser() {
		const credentials = await this.createUserAccount();
		await LoginPage.login( credentials.username, credentials.password );
	}

	async adminBot() {
		return await createApiClient();
	}

	async otherBot() {
		const credentials = await this.createUserAccount();
		return await createApiClient( { username: credentials.username, password: credentials.password } );
	}
}

export default new TestAccounts();
