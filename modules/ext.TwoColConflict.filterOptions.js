( function( mw, $ ) {
	var autoScroll = new mw.libs.twoColConflict.AutoScroll();

	$( function() {
		// show filter options when js is available
		$( '.mw-twocolconflict-filter-options' ).css( 'display', 'table' );
		// set some styles only with js enabled
		$( '.mw-twocolconflict-editor-col' ).addClass( 'mw-twocolconflict-js' );

		$( 'input[name="mw-twocolconflict-same"]' ).change( function() {
			var $changeDiv = autoScroll.getFirstVisibleChangesElement(),
				manualOffset;

			manualOffset = autoScroll.getDivTopOffset(
				$changeDiv,
				$( '.mw-twocolconflict-changes-editor' )
			);

			if ( $( this ).val() === 'show' ) {
				$( '.mw-twocolconflict-diffchange-same-collapsed' ).hide();
				$( '.mw-twocolconflict-diffchange-same-full' ).show();
			} else {
				$( '.mw-twocolconflict-diffchange-same-full' ).hide();
				$( '.mw-twocolconflict-diffchange-same-collapsed' ).show();
			}

			// wait for expanding animations to be finished
			$( '.mw-twocolconflict-diffchange-same-full' ).promise().done( function() {
				autoScroll.scrollToChangeWithOffset( $changeDiv, manualOffset );
			} );
		} );

		$( '.mw-twocolconflict-diffchange-same-collapsed' ).click( function() {
			var $changeDiv = $( this ).parent(),
				manualOffset;

			manualOffset = autoScroll.getDivTopOffset(
				$changeDiv,
				$( '.mw-twocolconflict-changes-editor' )
			);

			$( 'input[name="mw-twocolconflict-same"]' )[ 0 ].click();

			// wait for expanding animations to be finished
			$( '.mw-twocolconflict-diffchange-same-full' ).promise().done( function() {
				autoScroll.scrollToChangeWithOffset( $changeDiv, manualOffset );
			} );
		} );
	} );
}( mediaWiki, jQuery ) );
