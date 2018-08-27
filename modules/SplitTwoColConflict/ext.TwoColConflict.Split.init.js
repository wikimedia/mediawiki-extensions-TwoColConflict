( function ( mw, $ ) {
	'use strict';

	function initColumnSelection() {
		var $switches = $( '.mw-twocolconflict-split-selection' ),
			$radioButtons = $switches.find( 'input' );

		$radioButtons.on( 'change', function () {
			var $switch = $( this ),
				$row = $switch.closest( '.mw-twocolconflict-split-row' ),
				$selectedColumn, $unselectedColumn;

			if ( $switch.val() === 'your' ) {
				$selectedColumn = $row.find( '.mw-twocolconflict-split-add' );
				$unselectedColumn = $row.find( '.mw-twocolconflict-split-delete' );
			} else {
				$selectedColumn = $row.find( '.mw-twocolconflict-split-delete' );
				$unselectedColumn = $row.find( '.mw-twocolconflict-split-add' );
			}
			$selectedColumn
				.addClass( 'mw-twocolconflict-split-selected' )
				.removeClass( 'mw-twocolconflict-split-unselected' );
			$unselectedColumn
				.removeClass( 'mw-twocolconflict-split-selected' )
				.addClass( 'mw-twocolconflict-split-unselected' );
		} );

		$switches.find( 'input:checked' ).trigger( 'change' );
	}

	function initTour() {
		var $body = $( 'body' ), $helpBtn, tour,
			Tour = mw.libs.twoColConflict.split.Tour,
			settings = new mw.libs.twoColConflict.Settings();

		tour = Tour.init(
			mw.msg( 'twocolconflict-split-tour-dialog-header' ),
			'mw-twocolconflict-split-tour-slide-1',
			mw.msg( 'twocolconflict-split-tour-dialog-message' )
		);

		tour.addTourPopup(
			mw.msg( 'twocolconflict-split-tour-popup1-header' ),
			mw.msg( 'twocolconflict-split-tour-popup1-message' ),
			$body.find( '.mw-twocolconflict-split-your-version-header' )
		);

		tour.addTourPopup(
			mw.msg( 'twocolconflict-split-tour-popup2-header' ),
			mw.msg( 'twocolconflict-split-tour-popup2-message' ),
			$body.find( '.mw-twocolconflict-split-selection' ).first()
		);

		tour.addTourPopup(
			mw.msg( 'twocolconflict-split-tour-popup3-header' ),
			mw.msg( 'twocolconflict-split-tour-popup3-message' ),
			$body.find( '.mw-twocolconflict-diffchange' ).first()
		);

		$helpBtn = tour.getHelpButton();
		$( '.mw-twocolconflict-split-flex-header' ).prepend( $helpBtn );

		if ( !settings.shouldHideHelpDialogue() ) {
			// Delay slightly so that the first tour dialog
			// has the correct size when it opens
			// Todo: Look into a better fix
			setTimeout( function () {
				tour.showTour();
				settings.setHideHelpDialogue( true );
			}, 250 );
		}
	}

	$( function () {
		initColumnSelection();
		initTour();
	} );

}( mediaWiki, jQuery ) );
