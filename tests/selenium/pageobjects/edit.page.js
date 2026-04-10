import Page from 'wdio-mediawiki/Page';

// Adapted from mw-core edit.page.js

class EditPage extends Page {
	get content() {
		return $( '#wpTextbox1' );
	}

	get save() {
		return $( '#wpSave' );
	}

	async openForEditing( title ) {
		await super.openTitle( title, { action: 'edit', vehidebetadialog: 1, hidewelcomedialog: 1 } );
	}

	async openSectionForEditing( title, section ) {
		await super.openTitle( title, { action: 'edit', section: section, vehidebetadialog: 1, hidewelcomedialog: 1 } );
	}
}

export default new EditPage();
