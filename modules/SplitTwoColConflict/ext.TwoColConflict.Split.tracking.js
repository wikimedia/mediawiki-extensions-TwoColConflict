( function () {

	'use strict';

	var finalExitEvent = null;

	function wasEdited( $column ) {
		var $editor = $column.find( '.mw-twocolconflict-split-editor' ),
			$resetEditorText = $column.find( '.mw-twocolconflict-split-reset-editor-text' );
		return $editor.val() !== $resetEditorText.text();
	}

	function getEditStatistics() {
		var $rows = $( '.mw-twocolconflict-split-row' ),
			statistics = [];

		$rows.each( function () {
			var rowState = {};
			if ( $( this ).children( '.mw-twocolconflict-split-copy' ).length ) {
				rowState.copyEdited = wasEdited( $( this ).find( '.mw-twocolconflict-split-copy' ) );
			} else {
				rowState = {
					otherEdited: wasEdited( $( this ).find( '.mw-twocolconflict-split-delete' ) ),
					yoursEdited: wasEdited( $( this ).find( '.mw-twocolconflict-split-add' ) ),
					sideSelected: $( this ).find( 'input:checked' ).first().val()
				};
			}
			statistics.push( rowState );
		} );

		return statistics;
	}

	function recordExitStatistics() {
		mw.track( 'event.TwoColConflictExit', {
			action: finalExitEvent || 'unknown',
			startTime: $( 'input[name ="wpStarttime"]' ).val() || '',
			baseRevisionId: parseInt( $( 'input[name ="parentRevId"]' ).val() ),
			latestRevisionId: parseInt( $( 'input[name ="editRevId"]' ).val() ),
			pageNs: parseInt( mw.config.get( 'wgNamespaceNumber' ) ),
			pageTitle: mw.config.get( 'wgTitle' ),
			selections: getEditStatistics()
		} );
	}

	function initTrackingListeners() {
		$( '#wpSave' ).on( 'click', function () {
			finalExitEvent = 'save';
		} );

		$( '#mw-editform-cancel' ).on( 'click', function () {
			finalExitEvent = 'cancel';
		} );

		window.addEventListener( 'unload', recordExitStatistics );
	}

	module.exports = initTrackingListeners;
}() );
