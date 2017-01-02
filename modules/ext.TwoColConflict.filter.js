( function () {
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
	} );
}() );
