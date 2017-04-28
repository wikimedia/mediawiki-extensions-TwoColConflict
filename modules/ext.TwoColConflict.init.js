( function ( mw, $ ) {
	var autoScroll = new mw.libs.twoColConflict.AutoScroll(),
		helpDialog = mw.libs.twoColConflict.HelpDialog;

	function selectText( element ) {
		var range, selection;

		if ( document.body.createTextRange ) {
			range = document.body.createTextRange();
			range.moveToElementText( element );
			range.select();
		} else if ( window.getSelection ) {
			selection = window.getSelection();
			range = document.createRange();
			range.selectNodeContents( element );
			selection.removeAllRanges();
			selection.addRange( range );
		}
	}

	function initHelpDialog() {
		helpDialog.init( {
			name: 'twoColConflict',
			title: 'twoColConflict-tutorial',
			size: 'medium',
			prev: 'twoColConflict-previous-dialog',
			next: 'twoColConflict-next-dialog',
			close: 'twoColConflict-close-dialog',
			slides: [
				{
					message: 'twoColConflict-help-dialog-slide1',
					imageClass: 'mw-twocolconflict-help-dialog-slide-1',
					imageMode: 'landscape'
				},
				{
					message: 'twoColConflict-help-dialog-slide2',
					imageClass: 'mw-twocolconflict-help-dialog-slide-2',
					imageMode: 'landscape'
				},
				{
					message: 'twoColConflict-help-dialog-slide3',
					imageClass: 'mw-twocolconflict-help-dialog-slide-3',
					imageMode: 'landscape'
				},
				{
					message: 'twoColConflict-help-dialog-slide4',
					imageClass: 'mw-twocolconflict-help-dialog-slide-4',
					imageMode: 'landscape'
				}
			]
		} );

		$( 'button[name="mw-twocolconflict-show-help"]' ).click( function () {
			helpDialog.show();
		} );
	}

	/**
	 * Calculates the spacing between the bottom of the header and the top of the text editor
	 *
	 * @param {jQuery} $header
	 * @param {jQuery} $editor
	 * @return {number}
	 */
	function getSpaceBetweenHeaderAndEditor( $header, $editor ) {
		return $editor.offset().top - $header.offset().top - $header.height();
	}

	/**
	 * Calculates the height difference of two headers
	 *
	 * @param {jQuery} $header1
	 * @param {jQuery} $header2
	 * @return {number}
	 */
	function getHeaderHeightDiff( $header1, $header2 ) {
		return $header1.height() - $header2.height();
	}

	/**
	 * Adjusts the spacing below the editor column's header in order to synchronize the position of
	 * both columns and removes the editor column's left padding and spacing when it collapses below
	 * the changes column
	 */
	function adjustEditorColSpacing() {
		var $changesCol = $( '.mw-twocolconflict-changes-col' ),
			$editorCol = $( '.mw-twocolconflict-editor-col' ),
			$changesColHeader = $( '.mw-twocolconflict-changes-col .mw-twocolconflict-col-header' ),
			$editorColHeader = $( '.mw-twocolconflict-editor-col .mw-twocolconflict-col-header' ),
			$changesEditor = $( '.mw-twocolconflict-changes-editor' ),
			$wikiEditorToolbar = $( '#wikiEditor-ui-toolbar' ),
			toolbarHeight = $wikiEditorToolbar.length ? $wikiEditorToolbar.height() : $( '#toolbar' ).height();

		if ( $changesCol.position().left !== $editorCol.position().left ) {
			$editorColHeader.css( 'margin-bottom',
				getSpaceBetweenHeaderAndEditor( $changesColHeader, $changesEditor ) -
				getHeaderHeightDiff( $editorColHeader, $changesColHeader ) - toolbarHeight + 'px'
			);
			$editorCol.css( 'padding-left', '0.5em' );
		} else {
			$editorColHeader.css( 'margin-bottom', '10px' );
			$editorCol.css( 'padding-left', 0 );
		}
	}

	/**
	 * Open the hyperlinks of the edit summary in a new tab by default
	 */
	function addTargetToEditSummaryLinks() {
		$( '.mw-twocolconflict-edit-summary' ).find( 'a' ).attr( 'target', '_blank' );
	}

	$( function () {
		$( '.mw-twocolconflict-changes-editor' ).keydown( function( e ) {
			if ( e.ctrlKey && e.keyCode === 65 ) { // CTRL + A
				e.preventDefault();
				selectText( this );
			}
		} );

		autoScroll.setScrollBaseData();
		autoScroll.scrollToFirstOwnOrConflict();

		$( window ).on( 'resize', function() {
			autoScroll.setScrollBaseData();
			adjustEditorColSpacing();
		} );

		initHelpDialog();
		addTargetToEditSummaryLinks();

		if ( mw.config.get( 'wgTwoColConflictWikiEditor' ) ) {
			$( '#wpTextbox1' ).on( 'wikiEditor-toolbar-doneInitialSections', function () {
				adjustEditorColSpacing();
			} );
		} else {
			adjustEditorColSpacing();
		}
	} );
}( mediaWiki, jQuery ) );
