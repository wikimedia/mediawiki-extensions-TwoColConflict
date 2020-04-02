const EditConflictPage = require( '../pageobjects/editconflict.page' ),
	Util = require( 'wdio-mediawiki/Util' );

class TalkConflictPage {
	get draggableContainer() { return $( '.mw-twocolconflict-draggable' ); }
	get splitColumn() { return $( '.mw-twocolconflict-split-column' ); }

	createTalkPageConflict() {
		EditConflictPage.createConflict(
			'Line1\nLine2\nLine3\n',
			'Line1\nLine2\nLine3\nComment <span lang="de">A</span>',
			'Line1\nLine2\nLine3\nComment <span lang="en">B</span>',
			Util.getTestString( 'Talk:Test-conflict-' )
		);
	}

	editMyComment( newText ) {
		browser.pause( 500 );
		EditConflictPage.getEditButton( 'your' ).click();
		EditConflictPage.getEditor( 'your' ).setValue( newText );
		EditConflictPage.getSaveButton( 'your' ).click();
	}

	isOtherBlockFirst() {
		const rows = $$( '.mw-twocolconflict-single-column' );
		// FIXME: Note that this is fragile and assumes the conflict shows "copy", then "add/add".
		return rows[ 1 ].getAttribute( 'class' ).includes( 'mw-twocolconflict-split-delete' ) &&
			rows[ 2 ].getAttribute( 'class' ).includes( 'mw-twocolconflict-split-add' );
	}
}

module.exports = new TalkConflictPage();
