( function () {

	'use strict';

	var finalExitEvent = null;

	function recordExitStatistics() {
		/* eslint-disable camelcase */
		mw.track( 'event.TwoColConflictExit', {
			action: finalExitEvent || 'unknown',
			start_time: $( 'input[name ="wpStarttime"]' ).val() || '',
			base_revision_id: parseInt( $( 'input[name ="parentRevId"]' ).val() ),
			latest_revision_id: parseInt( $( 'input[name ="editRevId"]' ).val() ),
			page_ns: parseInt( mw.config.get( 'wgNamespaceNumber' ) ),
			page_title: mw.config.get( 'wgTitle' )
		} );
		/* eslint-enable camelcase */
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
