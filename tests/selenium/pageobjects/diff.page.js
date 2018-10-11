const Page = require( 'wdio-mediawiki/Page' );

class DiffPage extends Page {
	get diffView() { return browser.element( '#wikiDiff' ); }
	get diffEmpty() { return browser.element( '#wikiDiff .mw-diff-empty' ); }
}

module.exports = new DiffPage();
