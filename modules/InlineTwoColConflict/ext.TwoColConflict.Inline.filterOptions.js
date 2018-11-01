( function ( mw, $ ) {
	var autoScroll = new mw.libs.twoColConflict.AutoScroll();

	$( function () {
		$( 'input[name="mw-twocolconflict-show-changes"]' ).change( function () {
			if ( $( this ).val() === 'mine' ) {
				$( '.mw-twocolconflict-diffchange-foreign' ).slideUp();
			} else {
				$( '.mw-twocolconflict-diffchange-foreign' ).slideDown();
			}
		} );

		/**
		 * Either shows or hides the text surrounding the diff text
		 *
		 * @param {boolean} show
		 */
		function surroundingText( show ) {
			var $changeDiv = autoScroll.getFirstVisibleChangesElement();

			var manualOffset = autoScroll.getDivTopOffset(
				$changeDiv,
				$( '.mw-twocolconflict-changes-editor' )
			);

			if ( show ) {
				$( '.mw-twocolconflict-diffchange-same-collapsed' ).hide();
				$( '.mw-twocolconflict-diffchange-same-full' ).css( 'display', 'block' );
			} else {
				$( '.mw-twocolconflict-diffchange-same-full' ).hide();
				$( '.mw-twocolconflict-diffchange-same-collapsed' ).css( 'display', 'block' );
			}

			// wait for expanding animations to be finished
			$( '.mw-twocolconflict-diffchange-same-full' ).promise().done( function () {
				autoScroll.scrollToChangeWithOffset( $changeDiv, manualOffset );
			} );
		}

		$( 'input[name="mw-twocolconflict-same"]' ).click( function () {
			surroundingText( $( this ).val() === 'show' );
		} );

		var expandBtn = new OO.ui.ButtonInputWidget( {
			indicator: 'down',
			value: 0,
			name: 'mw-twocolconflict-expand-collapse',
			classes: [ 'mw-twocolconflict-expand-collapse-btn' ],
			title: mw.msg( 'twocolconflict-label-show-unchanged' )
		} );
		expandBtn.$element.children().attr( {
			'aria-label': expandBtn.getTitle(),
			'aria-expanded': false
		} );

		var collapseBtn = new OO.ui.ButtonInputWidget( {
			indicator: 'up',
			value: 1,
			name: 'mw-twocolconflict-expand-collapse',
			classes: [ 'mw-twocolconflict-expand-collapse-btn' ],
			title: mw.msg( 'twocolconflict-label-hide-unchanged' )
		} );
		collapseBtn.$element.children().attr( {
			'aria-label': collapseBtn.getTitle(),
			'aria-expanded': true
		} );

		$( '.mw-twocolconflict-diffchange-same-collapsed' ).prepend( expandBtn.$element );
		$( '.mw-twocolconflict-diffchange-same-full' ).prepend( collapseBtn.$element );

		$( 'button[name="mw-twocolconflict-expand-collapse"]' ).click( function () {
			$( 'input[name="mw-twocolconflict-same"]' )[ $( this ).val() ].click();
		} );

		// select 'hide' as the default option
		$( 'input[name="mw-twocolconflict-same"]' )[ 1 ].click();
	} );
}( mediaWiki, jQuery ) );
