( function () {
	$( function () {
		$( '.mw-twocolconflict-filter-options div' ).css( 'display', 'table-cell' );

		$( 'input[name="mw-twocolconflict-same"]' ).change(  function () {
			if ( $( this ).val() === 'show' ) {
				$( '.mw-twocolconflict-diffchange-same-collapsed' ).hide();
				$( '.mw-twocolconflict-diffchange-same-full' ).show();
			} else {
				$( '.mw-twocolconflict-diffchange-same-full' ).hide();
				$( '.mw-twocolconflict-diffchange-same-collapsed' ).show();
			}
		} );
	} );
}() );
