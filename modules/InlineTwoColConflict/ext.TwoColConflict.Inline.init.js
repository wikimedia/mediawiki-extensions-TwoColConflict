( function ( mw, $ ) {
	var settings = new mw.libs.twoColConflict.Settings(),
		autoScroll = new mw.libs.twoColConflict.AutoScroll(),
		helpDialog = mw.libs.twoColConflict.HelpDialog,
		BaseVersionSelector = mw.libs.twoColConflict.BaseVersionSelector;

	mw.loader.load( 'ext.TwoColConflict.Inline.BaseVersionSelectorCss' );

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
			name: 'twocolconflict',
			title: 'twocolconflict-tutorial',
			size: 'medium',
			prev: 'twocolconflict-previous-dialog',
			next: 'twocolconflict-next-dialog',
			close: 'twocolconflict-close-dialog',
			slides: [
				{
					message: 'twocolconflict-help-dialog-slide1',
					parameters: [],
					imageClass: 'mw-twocolconflict-help-dialog-slide-1',
					imageMode: 'landscape'
				},
				{
					message: 'twocolconflict-help-dialog-slide2',
					parameters: [],
					imageClass: 'mw-twocolconflict-help-dialog-slide-2',
					imageMode: 'landscape'
				},
				{
					message: 'twocolconflict-help-dialog-slide3',
					parameters: [],
					imageClass: 'mw-twocolconflict-help-dialog-slide-3',
					imageMode: 'landscape'
				},
				{
					message: 'twocolconflict-help-dialog-slide4',
					parameters: [],
					imageClass: 'mw-twocolconflict-help-dialog-slide-4',
					imageMode: 'landscape'
				},
				{
					message: 'twocolconflict-help-dialog-slide5',
					parameters: [ mw.config.get( 'wgTwoColConflictSubmitLabel' ) ],
					imageClass: 'mw-twocolconflict-help-dialog-slide-5',
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
	 *
	 * When called for the first time and if the WikiEditor is enabled the WikiEditor's toolbar
	 * height will be hardcoded in case the WikiEditor wasn't loaded yet, any subsequent calls will fetch
	 * the height dynamically
	 *
	 *  @param {boolean} $isInit
	 */
	function adjustEditorColSpacing( $isInit ) {
		var $changesCol = $( '.mw-twocolconflict-changes-col' ),
			$editorCol = $( '.mw-twocolconflict-editor-col' ),
			$changesColHeader = $( '.mw-twocolconflict-changes-col .mw-twocolconflict-col-header' ),
			$editorColHeader = $( '.mw-twocolconflict-editor-col .mw-twocolconflict-col-header' ),
			$changesEditor = $( '.mw-twocolconflict-changes-editor' ),
			$wikiEditorToolbar = $( '#wikiEditor-ui-toolbar' ),
			toolbarHeight;

		if ( $isInit ) {
			toolbarHeight = mw.config.get( 'wgTwoColConflictWikiEditor' ) ? 32 : $( '#toolbar' ).height();
		} else {
			toolbarHeight = $wikiEditorToolbar.length ? $wikiEditorToolbar.height() : $( '#toolbar' ).height();
		}

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

	function redrawPage() {
		autoScroll.setScrollBaseData();
		adjustEditorColSpacing( false );
	}

	function disableEditButtons() {
		if ( mw.config.get( 'wgTwoColConflictTestMode' ) ) {
			OO.ui.infuse( 'wpTestPreviewWidget' ).setDisabled( true );
			return;
		}
		OO.ui.infuse( 'wpSaveWidget' ).setDisabled( true );
		OO.ui.infuse( 'wpPreviewWidget' ).setDisabled( true );
		OO.ui.infuse( 'wpDiffWidget' ).setDisabled( true );
	}

	function enableEditButtons() {
		if ( mw.config.get( 'wgTwoColConflictTestMode' ) ) {
			OO.ui.infuse( 'wpTestPreviewWidget' ).setDisabled( false );
			return;
		}
		OO.ui.infuse( 'wpSaveWidget' ).setDisabled( false );
		OO.ui.infuse( 'wpPreviewWidget' ).setDisabled( false );
		OO.ui.infuse( 'wpDiffWidget' ).setDisabled( false );
	}

	function beforeBaseVersionSelection() {
		disableEditButtons();
	}

	function afterBaseVersionSelection() {
		enableEditButtons();
		$( '#wpTextbox1' ).addClass( 'mw-twocolconflict-after-base-selection' );
		redrawPage();
		autoScroll.scrollToFirstOwnOrConflict();
		$( '.mw-twocolconflict-changes-editor' ).focus();
	}

	function initAndShowBaseVersionSelector() {
		var versionSelector = new BaseVersionSelector(),
			windowManager = new OO.ui.WindowManager( {
				modal: false
			} );
		beforeBaseVersionSelection();
		$( '.mw-twocolconflict-col-header' ).append( windowManager.$element );
		versionSelector.setCloseCallback( afterBaseVersionSelection );
		windowManager.addWindows( [ versionSelector ] );
		windowManager.openWindow( versionSelector );
	}

	$( function () {
		$( '.mw-twocolconflict-changes-editor' ).keydown( function ( e ) {
			if ( e.ctrlKey && e.keyCode === 65 ) { // CTRL + A
				e.preventDefault();
				selectText( this );
			}
		} );

		$( window ).on( 'resize', redrawPage );

		// set label for the textbox to the editor header text
		$( '#wpTextbox1' ).attr( {
			'aria-labelledby': 'mw-twocolconflict-edit-header'
		} );

		initAndShowBaseVersionSelector();
		initHelpDialog();
		addTargetToEditSummaryLinks();
		adjustEditorColSpacing( true );

		if ( !settings.shouldHideHelpDialogue() ) {
			helpDialog.show();
			settings.setHideHelpDialogue( true );
		}
	} );
}( mediaWiki, jQuery ) );
