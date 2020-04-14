'use strict';

var finalExitEvent = null;

function recordExitStatistics() {
	/* eslint-disable camelcase */
	mw.track( 'event.TwoColConflictExit', {
		action: finalExitEvent || 'unknown',
		start_time_ts_ms: parseInt( $( 'input[name="wpStarttime"]' ).val() ) * 1000 || 0,
		base_rev_id: parseInt( $( 'input[name="parentRevId"]' ).val() ),
		latest_rev_id: parseInt( $( 'input[name="editRevId"]' ).val() ),
		page_namespace: parseInt( mw.config.get( 'wgNamespaceNumber' ) ),
		page_title: mw.config.get( 'wgTitle' ),
		session_token: mw.user.sessionId()
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
