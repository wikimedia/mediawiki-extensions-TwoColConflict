( function ( mw, $ ) {
	var autoScroll = new mw.libs.twoColConflict.AutoScroll(),
		helpDialog = mw.libs.twoColConflict.HelpDialog;

	function selectText( element ) {
		var range, selection;

		if ( document.body.createTextRange ) {
			range = document.body.createTextRange();
			range.moveToElementText( element );
			range.select();
		} else if ( window.getSelection ) {
			selection = window.getSelection();
			range = document.createRange();
			range.selectNodeContents( element );
			selection.removeAllRanges();
			selection.addRange( range );
		}
	}

	function initHelpDialog() {
		helpDialog.init( {
			name: 'twoColConflict',
			title: 'twoColConflict-tutorial',
			size: 'medium',
			prev: 'twoColConflict-previous-dialog',
			next: 'twoColConflict-next-dialog',
			close: 'twoColConflict-close-dialog',
			slides: [
				{
					message: 'twoColConflict-help-dialog-slide1',
					imageClass: 'mw-twocolconflict-help-dialog-slide-1',
					imageMode: 'landscape'
				},
				{
					message: 'twoColConflict-help-dialog-slide2',
					imageClass: 'mw-twocolconflict-help-dialog-slide-2',
					imageMode: 'landscape'
				},
				{
					message: 'twoColConflict-help-dialog-slide3',
					imageClass: 'mw-twocolconflict-help-dialog-slide-3',
					imageMode: 'landscape'
				},
				{
					message: 'twoColConflict-help-dialog-slide4',
					imageClass: 'mw-twocolconflict-help-dialog-slide-4',
					imageMode: 'landscape'
				}
			]
		} );

		$( 'button[name="mw-twocolconflict-show-help"]' ).click( function () {
			helpDialog.show();
		} );
	}

	$( function () {
		$( '.mw-twocolconflict-changes-editor' ).keydown( function( e ) {
			if ( e.ctrlKey && e.keyCode === 65 ) { // CTRL + A
				e.preventDefault();
				selectText( this );
			}
		} );

		autoScroll.setScrollBaseData();
		autoScroll.scrollToFirstOwnOrConflict();

		$( window ).on( 'resize', function() {
			autoScroll.setScrollBaseData();
		} );

		initHelpDialog();
	} );
}( mediaWiki, jQuery ) );
