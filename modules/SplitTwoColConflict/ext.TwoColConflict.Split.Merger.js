( function () {

	/**
	 * @param {string} str
	 * @param {number} num
	 * @return {string}
	 */
	function repeat( str, num ) {
		var out = '';
		for ( var i = 0; i < num; i++ ) {
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

		$rows.each( function ( $index, $row ) {
			$( $row ).find( '.mw-twocolconflict-split-column' ).each( function ( $index, $column ) {
				if (
					$( $column ).hasClass( 'mw-twocolconflict-split-copy' ) ||
					$( $column ).hasClass( 'mw-twocolconflict-split-selected' )
				) {
					var line = $( $column ).find( '.mw-twocolconflict-split-editor' )
						.val().trim( '\r\n' );

					if ( line !== '' ) {
						var $extraLineFeeds = $( $column ).find(
							'[name^=\'mw-twocolconflict-split-linefeeds\']'
						);

						if ( $extraLineFeeds !== null ) {
							line += repeat( '\n', $extraLineFeeds.val() );
						}

						textLines.push( line );
					}
				}
			} );
		} );

		return textLines.join( '\n' );
	}

	mw.libs.twoColConflict = mw.libs.twoColConflict || {};
	mw.libs.twoColConflict.split = mw.libs.twoColConflict.split || {};
	mw.libs.twoColConflict.split.merger = merger;
}() );
