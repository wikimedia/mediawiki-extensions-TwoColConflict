import { getTestString } from 'wdio-mediawiki/Util';
import EditConflictPage from '../pageobjects/editconflict.page.js';

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
			( getTestString( 'Talk:Test-conflict-' ) )
		);
		await this.talkRow.waitForDisplayed();
	}

	async editMyComment( newText ) {
		await EditConflictPage.getEditButton( 'your' ).click();
		await EditConflictPage.getEditor( 'your' ).setValue( newText );
		await EditConflictPage.getSaveButton( 'your' ).click();
	}

	async isOtherBlockFirst() {
		const rows = await $$( '.mw-twocolconflict-single-column' );
		if ( rows.length < 3 ) {
			return false;
		}
		// FIXME: This assumes that the tested conflict shows "copy" and then the conflict.
		const class1 = await rows[ 1 ].getAttribute( 'class' );
		const class2 = await rows[ 2 ].getAttribute( 'class' );
		return class1.includes( 'mw-twocolconflict-split-delete' ) &&
			class2.includes( 'mw-twocolconflict-split-add' );
	}

	async isYourBlockFirst() {
		const rows = await $$( '.mw-twocolconflict-single-column' );
		if ( rows.length < 3 ) {
			return false;
		}
		// FIXME: This assumes that the tested conflict shows "copy" and then the conflict.
		const class1 = await rows[ 1 ].getAttribute( 'class' );
		const class2 = await rows[ 2 ].getAttribute( 'class' );
		return class1.includes( 'mw-twocolconflict-split-add' ) &&
			class2.includes( 'mw-twocolconflict-split-delete' );
	}
}

export default new TalkConflictPage();
