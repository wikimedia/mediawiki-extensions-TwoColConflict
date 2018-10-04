const Page = require( 'wdio-mediawiki/Page' );

class FinishedConflictPage extends Page {
	get pageText() { return browser.element( '.mw-parser-output p' ); }
}

module.exports = new FinishedConflictPage();
