const Page = require( 'wdio-mediawiki/Page' );

class PreviewPage extends Page {
	get previewView() { return browser.element( '#wikiPreview' ); }
	get previewText() { return browser.element( '#wikiPreview .mw-parser-output' ); }
}

module.exports = new PreviewPage();
