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
	 * @param {jQuery} $rows
	 * @return {string}
	 */
	function merger( $rows ) {
		var textLines = [];

		$rows.find(
			'.mw-twocolconflict-split-column.mw-twocolconflict-split-copy, ' +
			'.mw-twocolconflict-split-column.mw-twocolconflict-split-selected'
		).each( function ( index, element ) {
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
