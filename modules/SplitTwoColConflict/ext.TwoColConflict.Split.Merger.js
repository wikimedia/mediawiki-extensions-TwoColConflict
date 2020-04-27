/**
 * @param {string} num
 * @return {string}
 */
function lineFeeds( num ) {
	var out = '';
	num = parseInt( num, 10 );
	while ( num-- ) {
		out += '\n';
	}
	return out;
}

/**
 * @param {jQuery} $selected Columns to merge
 * @return {string}
 */
function merger( $selected ) {
	var textLines = [];

	$selected.each( function ( index, element ) {
		var line = $( element ).find( '.mw-twocolconflict-split-editor' ).val()
				.replace( /[\r\n]+$/, '' ),
			emptiedByUser = line === '',
			$extraLineFeeds = $( element ).find( '[name^="mw-twocolconflict-split-linefeeds"]' );

		if ( $extraLineFeeds.length ) {
			var lf = $extraLineFeeds.val().split( ',', 2 );
			// "Before" and "after" are intentionally flipped, because "before" is very rare
			if ( 1 in lf ) {
				if ( lf[ 1 ] === 'was-empty' ) {
					emptiedByUser = false;
				} else {
					line = lineFeeds( lf[ 1 ] ) + line;
				}
			}
			line += lineFeeds( lf[ 0 ] );
		}

		if ( !emptiedByUser ) {
			textLines.push( line );
		}
	} );

	return textLines.join( '\n' );
}

module.exports = merger;
