'use strict';

// Make sure we don't try to save an option for anonymous users
if ( !mw.user.isAnon() ) {
	// It's fine to run this even when the element doesn't exist
	$( '.mw-twocolconflict-core-ui-hint input[ type="checkbox" ]' ).change( function () {
		if ( this.checked ) {
			( new mw.Api() ).saveOption( 'userjs-twocolconflict-hide-core-hint', '1' );
		}
	} );
}
