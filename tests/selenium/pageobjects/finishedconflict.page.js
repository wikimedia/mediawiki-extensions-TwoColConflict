'use strict';

const Page = require( 'wdio-mediawiki/Page' ),
	Util = require( 'wdio-mediawiki/Util' );

class FinishedConflictPage extends Page {
	get pageWikitext() {
		Util.waitForModuleState( 'mediawiki.base' );

		const result = browser.executeAsync( ( done ) =>
			mw.loader.using( 'mediawiki.api' ).then( () =>
				new mw.Api().get( {
					action: 'query',
					formatversion: 2,
					prop: 'revisions',
					revids: mw.config.get( 'wgCurRevisionId' ),
					rvprop: 'content',
					rvslots: 'main'
				} ).done(
					( data ) => done( data )
				)
			)
		);

		return result.query.pages[ 0 ].revisions[ 0 ].slots.main.content;
	}
}

module.exports = new FinishedConflictPage();
