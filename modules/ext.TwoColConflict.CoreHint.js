'use strict';

var HIDE_CORE_HINT_PREFERENCE = 'userjs-twocolconflict-hide-core-hint';

$( function () {
	$( '.mw-twocolconflict-core-ui-hint input[ type="checkbox" ]' ).change( function () {
		if ( this.checked ) {
			if ( !mw.user.isAnon() ) {
				( new mw.Api() ).saveOption( HIDE_CORE_HINT_PREFERENCE, '1' );
			}
		}
	} );
} );
