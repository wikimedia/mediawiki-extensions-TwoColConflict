'use strict';

const Page = require( 'wdio-mediawiki/Page' ),
	Util = require( 'wdio-mediawiki/Util' );

class FinishedConflictPage extends Page {
	async pageWikitext() {
		await Util.waitForModuleState( 'mediawiki.base' );

		const result = await browser.execute( async () => {
			await mw.loader.using( 'mediawiki.api' );
			return new mw.Api().get( {
				action: 'query',
				formatversion: 2,
				prop: 'revisions',
				revids: mw.config.get( 'wgCurRevisionId' ),
				rvprop: 'content',
				rvslots: 'main'
			} );
		} );
		return result.query.pages[ 0 ].revisions[ 0 ].slots.main.content;
	}
}

module.exports = new FinishedConflictPage();
