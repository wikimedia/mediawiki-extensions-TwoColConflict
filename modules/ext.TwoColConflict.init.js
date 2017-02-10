( function ( mw, $ ) {
	var autoScroll = new mw.libs.twoColConflict.AutoScroll();

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

		$(
			'.mw-twocolconflict-diffchange-foreign, ' +
			'.mw-twocolconflict-diffchange-own'
		).click( function() {
			autoScroll.scrollToConflictWithData( $( this ) );
		} );
	} );
}( mediaWiki, jQuery ) );
