import Page from 'wdio-mediawiki/Page';
import { waitForModuleState } from 'wdio-mediawiki/Util';

class FinishedConflictPage extends Page {
	async pageWikitext() {
		// Wait for the save navigation to complete and wgCurRevisionId to be set
		await browser.waitUntil(
			async () => {
				const revId = await browser.execute( () => typeof mw !== 'undefined' &&
					mw.config && mw.config.get( 'wgCurRevisionId' ) );
				return typeof revId === 'number' && revId > 0;
			},
			{ timeoutMsg: 'wgCurRevisionId was not set after page save' }
		);

		await waitForModuleState( 'mediawiki.base' );

		const result = await browser.executeAsync( ( callback ) => {
			mw.loader.using( 'mediawiki.api' ).then( () => {
				new mw.Api().get( {
					action: 'query',
					formatversion: 2,
					prop: 'revisions',
					revids: mw.config.get( 'wgCurRevisionId' ),
					rvprop: 'content',
					rvslots: 'main'
				} ).then( callback );
			} );
		} );
		return result.query.pages[ 0 ].revisions[ 0 ].slots.main.content;
	}
}

export default new FinishedConflictPage();
