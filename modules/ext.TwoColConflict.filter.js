( function ( $ ) {
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
		$( '.mw-twocolconflict-filter-options div' ).css( 'display', 'table-cell' );

		$( 'input[name="mw-twocolconflict-same"]' ).change( function () {
			if ( $( this ).val() === 'show' ) {
				$( '.mw-twocolconflict-diffchange-same-collapsed' ).slideUp();
				$( '.mw-twocolconflict-diffchange-same-full' ).slideDown();
			} else {
				$( '.mw-twocolconflict-diffchange-same-full' ).slideUp();
				$( '.mw-twocolconflict-diffchange-same-collapsed' ).slideDown();
			}
		} );

		$( '.mw-twocolconflict-diffchange-same-collapsed' ).click( function () {
			$( 'input[name="mw-twocolconflict-same"]' )[ 0 ].closest( 'label' ).click();
		} );

		$( '.mw-twocolconflict-changes-editor' ).keydown( function( e ) {
			if ( e.ctrlKey && e.keyCode === 65 ) { // CTRL + A
				e.preventDefault();
				selectText( this );
			}
		} );
	} );
}( jQuery ) );
