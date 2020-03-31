const Page = require( 'wdio-mediawiki/Page' );

class FinishedConflictPage extends Page {
	get pageText() { return $( '.mw-parser-output' ); }
}

module.exports = new FinishedConflictPage();
