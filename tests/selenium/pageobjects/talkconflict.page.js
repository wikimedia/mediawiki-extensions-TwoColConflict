'use strict';

const EditConflictPage = require( '../pageobjects/editconflict.page' ),
	Util = require( 'wdio-mediawiki/Util' );

class TalkConflictPage {
	get talkRow() {
		return $( '.mw-twocolconflict-conflicting-talk-row' );
	}

	get splitColumn() {
		return $( '.mw-twocolconflict-split-column' );
	}

	get orderSelector() {
		return $( '.mw-twocolconflict-order-selector' );
	}

	get keepAfterButton() {
		return $( '.mw-twocolconflict-order-selector [value="no-change"]' );
	}

	get moveBeforeButton() {
		return $( '.mw-twocolconflict-order-selector [value="reverse"]' );
	}

	get swapButton() {
		return $( '.mw-twocolconflict-single-swap-button' );
	}

	async createTalkPageConflict() {
		await EditConflictPage.createConflict(
			'Line1\nLine2\nLine3\n',
			'Line1\nLine2\nLine3\nComment <span lang="de">A</span>',
			'Line1\nLine2\nLine3\nComment <span lang="en">B</span>',
			( Util.getTestString( 'Talk:Test-conflict-' ) )
		);
		await this.talkRow.waitForDisplayed();
	}

	async editMyComment( newText ) {
		await EditConflictPage.getEditButton( 'your' ).click();
		await EditConflictPage.getEditor( 'your' ).setValue( newText );
		await EditConflictPage.getSaveButton( 'your' ).click();
	}

	async isOtherBlockFirst() {
		const rows = $$( '.mw-twocolconflict-single-column' );
		// FIXME: This assumes that the tested conflict shows "copy" and then the conflict.
		return ( await rows[ 1 ].getAttribute( 'class' ) ).includes( 'mw-twocolconflict-split-delete' ) &&
			( await rows[ 2 ].getAttribute( 'class' ) ).includes( 'mw-twocolconflict-split-add' );
	}

	async isYourBlockFirst() {
		const rows = $$( '.mw-twocolconflict-single-column' );
		// FIXME: This assumes that the tested conflict shows "copy" and then the conflict.
		return ( await rows[ 1 ].getAttribute( 'class' ) ).includes( 'mw-twocolconflict-split-add' ) &&
			( await rows[ 2 ].getAttribute( 'class' ) ).includes( 'mw-twocolconflict-split-delete' );
	}
}

module.exports = new TalkConflictPage();
