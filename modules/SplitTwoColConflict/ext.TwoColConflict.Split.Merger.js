( function () {

	/**
	 * @param {string} str
	 * @param {number} num
	 * @return {string}
	 */
	function repeat( str, num ) {
		var out = '';
		while ( num-- ) {
			out += str;
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
			var line = $( element ).find( '.mw-twocolconflict-split-editor' )
				.val().trim( '\r\n' );

			if ( line !== '' ) {
				var $extraLineFeeds = $( element ).find(
					'[name^="mw-twocolconflict-split-linefeeds"]'
				);

				if ( $extraLineFeeds.length ) {
					line += repeat( '\n', $extraLineFeeds.val() );
				}

				textLines.push( line );
			}
		} );

		return textLines.join( '\n' );
	}

	module.exports = merger;
}() );
