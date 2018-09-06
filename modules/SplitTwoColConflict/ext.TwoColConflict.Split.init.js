( function ( mw, $ ) {
	'use strict';

	/**
	 * @param {jQuery} $column
	 * @return {OO.ui.Element}
	 */
	function getColumnEditButton( $column ) {
		return OO.ui.ButtonWidget.static.infuse(
			$column.find( '.mw-twocolconflict-split-edit-button' )
		);
	}

	/**
	 * @param {jQuery} $selectedColumn
	 * @param {jQuery} $unselectedColumn
	 */
	function setColumnEditButtonState( $selectedColumn, $unselectedColumn ) {
		getColumnEditButton( $selectedColumn ).setDisabled( false );
		getColumnEditButton( $unselectedColumn ).setDisabled( true );
	}

	/**
	 * @return {String}
	 */
	function getEditorFontClass() {
		return $( '.mw-twocolconflict-split-editor' ).attr( 'class' )
			.replace( 'mw-twocolconflict-split-editor', '' )
			.trim();
	}

	/**
	 * @param {jQuery} $row
	 */
	function enableEditing( $row ) {
		$row.addClass( 'mw-twocolconflict-split-editing' );
		$row.find( '.mw-twocolconflict-split-editable' ).addClass( getEditorFontClass() );
	}

	/**
	 * @param {jQuery} $row
	 */
	function disableEditing( $row ) {
		var $selected = $row.find( '.mw-twocolconflict-split-selected, .mw-twocolconflict-split-copy' ),
			$diffText = $selected.find( '.mw-twocolconflict-split-difftext' ),
			$editor = $selected.find( '.mw-twocolconflict-split-editor' );

		$diffText.text( $editor.val() );

		$row.removeClass( 'mw-twocolconflict-split-editing' );
		$row.find( '.mw-twocolconflict-split-editable' ).removeClass( getEditorFontClass() );
	}

	function initButtonEvents() {
		$( '.mw-twocolconflict-split-edit-button' ).each( function () {
			var button = OO.ui.ButtonWidget.static.infuse( this );
			button.on( 'click', function () {
				enableEditing( button.$element.closest( '.mw-twocolconflict-split-row' ) );
			} );
		} );

		$( '.mw-twocolconflict-split-save-button' ).each( function () {
			var button = OO.ui.ButtonWidget.static.infuse( this );
			button.on( 'click', function () {
				disableEditing( button.$element.closest( '.mw-twocolconflict-split-row' ) );
			} );
		} );
	}

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
				setColumnEditButtonState( $selectedColumn, $unselectedColumn );
			} else {
				$selectedColumn = $row.find( '.mw-twocolconflict-split-delete' );
				$unselectedColumn = $row.find( '.mw-twocolconflict-split-add' );
				setColumnEditButtonState( $selectedColumn, $unselectedColumn );
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
			settings = new mw.libs.twoColConflict.Settings(),
			windowManager = new OO.ui.WindowManager();

		tour = Tour.init(
			mw.msg( 'twocolconflict-split-tour-dialog-header' ),
			'mw-twocolconflict-split-tour-slide-1',
			mw.msg( 'twocolconflict-split-tour-dialog-message' ),
			windowManager
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
			tour.showTour();
			settings.setHideHelpDialogue( true );
		}
	}

	$( function () {
		initColumnSelection();
		initButtonEvents();
		initTour();
	} );

}( mediaWiki, jQuery ) );
