const Page = require( 'wdio-mediawiki/Page' );

// Adapted from mw-core edit.page.js

class EditPage extends Page {
	get content() { return browser.element( '#wpTextbox1' ); }
	get save() { return browser.element( '#wpSave' ); }

	openForEditing( title ) {
		super.openTitle( title, { action: 'edit', vehidebetadialog: 1, hidewelcomedialog: 1 } );
	}

	openSectionForEditing( title, section ) {
		super.openTitle( title, { action: 'edit', section: section, vehidebetadialog: 1, hidewelcomedialog: 1 } );
	}
}

module.exports = new EditPage();
