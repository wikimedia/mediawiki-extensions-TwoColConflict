( function () {
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
	 * @return {string}
	 */
	function getEditorFontClass() {
		return $( '.mw-twocolconflict-split-editor' ).attr( 'class' )
			.replace( 'mw-twocolconflict-split-editor', '' )
			.trim();
	}

	function expandText( $row ) {
		$row.find( '.mw-twocolconflict-split-collapsed' )
			.removeClass( 'mw-twocolconflict-split-collapsed' )
			.addClass( 'mw-twocolconflict-split-expanded' );
	}

	function collapseText( $row ) {
		$row.find( '.mw-twocolconflict-split-expanded' )
			.removeClass( 'mw-twocolconflict-split-expanded' )
			.addClass( 'mw-twocolconflict-split-collapsed' );
	}

	/**
	 * @param {jQuery} $row
	 */
	function enableEditing( $row ) {
		var $selected = $row.find( '.mw-twocolconflict-split-selected, .mw-twocolconflict-split-copy' ),
			originalHeight = $selected.find( '.mw-twocolconflict-split-editable' ).height();

		expandText( $row );
		$row.addClass( 'mw-twocolconflict-split-editing' );
		$row.find( '.mw-twocolconflict-split-editable' ).addClass( getEditorFontClass() );

		$selected.find( 'textarea' ).each( function () {
			var $editor = $( this );
			if ( $editor.height() < originalHeight ) {
				$editor.height( originalHeight );
			}
		} );

		$selected.find( '.mw-twocolconflict-split-editor' ).focus();
	}

	/**
	 * @param {jQuery} $row
	 */
	function disableEditing( $row ) {
		$row.removeClass( 'mw-twocolconflict-split-editing' );
		$row.find( '.mw-twocolconflict-split-editable' ).removeClass( getEditorFontClass() );
	}

	/**
	 * @param {jQuery} $row
	 */
	function saveEditing( $row ) {
		var $selected = $row.find( '.mw-twocolconflict-split-selected, .mw-twocolconflict-split-copy' ),
			$diffText = $selected.find( '.mw-twocolconflict-split-difftext' ),
			$editor = $selected.find( '.mw-twocolconflict-split-editor' );

		$diffText.text( $editor.val() );
		disableEditing( $row );
	}

	/**
	 * @param {jQuery} $row
	 */
	function resetEditing( $row ) {
		var $selected = $row.find( '.mw-twocolconflict-split-selected, .mw-twocolconflict-split-copy' ),
			$diffText = $selected.find( '.mw-twocolconflict-split-difftext' ),
			$editor = $selected.find( '.mw-twocolconflict-split-editor' ),
			$resetDiffText = $selected.find( '.mw-twocolconflict-split-reset-diff-text' ),
			$resetEditorText = $selected.find( '.mw-twocolconflict-split-reset-editor-text' );

		$diffText.html( $resetDiffText.html() );
		$editor.val( $resetEditorText.text() );
		disableEditing( $row );
	}

	/**
	 * @param {jQuery} $row
	 */
	function resetWarning( $row ) {
		OO.ui.confirm(
			mw.msg( 'twocolconflict-split-reset-warning' ), {
				actions: [
					{
						label: mw.msg( 'twocolconflict-split-reset-warning-cancel' ),
						action: 'cancel'
					},
					{
						label: mw.msg( 'twocolconflict-split-reset-warning-accept' ),
						action: 'accept'
					}
				]
			}
		).done( function ( confirmed ) {
			if ( confirmed ) {
				resetEditing( $row );
			}
		} );
	}

	/**
	 * @param {OO.ui.ButtonWidget} button
	 */
	function addDisabledEditButtonToolTip( button ) {
		var popup = new OO.ui.PopupWidget( {
			$content: $( '<p>' ).html( mw.msg( 'twocolconflict-split-disabled-edit-tooltip' ) ),
			classes: [ 'mw-twocolconflict-split-disabled-edit-button-popup' ],
			padded: true,
			position: 'above'
		} );

		button.$element.append( popup.$element );
		button.$element.on( {
			mouseenter: function () {
				if ( $( this ).attr( 'aria-disabled' ) === 'true' ) {
					popup.toggle( true );
				}
			},
			mouseleave: function () {
				popup.toggle( false );
			}
		} );
	}

	function initButtonEvents() {
		[
			{ selector: '.mw-twocolconflict-split-edit-button', onclick: enableEditing },
			{ selector: '.mw-twocolconflict-split-save-button', onclick: saveEditing },
			{ selector: '.mw-twocolconflict-split-reset-button', onclick: resetWarning },
			{ selector: '.mw-twocolconflict-split-expand-button', onclick: expandText },
			{ selector: '.mw-twocolconflict-split-collapse-button', onclick: collapseText }
		].forEach( function ( button ) {
			$( button.selector ).each( function () {
				var widget = OO.ui.ButtonWidget.static.infuse( this ),
					$row = widget.$element.closest( '.mw-twocolconflict-split-row' );

				widget.on( 'click', function () {
					button.onclick( $row );
				} );

				if ( button.selector === '.mw-twocolconflict-split-edit-button' ) {
					addDisabledEditButtonToolTip( widget );
				}
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
		var $body = $( 'body' ),
			settings = new mw.libs.twoColConflict.Settings(),
			windowManager = new OO.ui.WindowManager();

		var tour = mw.libs.twoColConflict.split.Tour.init(
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

		var $helpBtn = tour.getHelpButton();
		$( '.mw-twocolconflict-split-flex-header' ).prepend( $helpBtn );

		if ( !settings.shouldHideHelpDialogue() ) {
			tour.showTour();
			settings.setHideHelpDialogue( true );
		}
	}

	function showPreview( $parsed ) {
		$( '#wikiPreview' ).remove();
		var $html = $( 'html' );
		var arrow = $html.attr( 'dir' ) === 'rtl' ? '←' : '→';

		var $note = $( '<div>' )
			.addClass( 'previewnote' )
			.append(
				$( '<h2>' )
					.attr( 'id', 'mw-previewheader' )
					.append( mw.msg( 'preview' ) ),
				$( '<p>' )
					.addClass( 'warningbox' )
					.append(
						mw.msg( 'previewnote' ),
						$( '<span>' )
							.addClass( 'mw-continue-editing' )
							.append(
								$( '<a>' )
									.attr( 'href', '#editform' )
									.append( ' ' + arrow + mw.msg( 'continue-editing' ) )
							)
					)
			);

		var $content = $( '<div>' )
			.addClass( 'mw-content-' + $html.attr( 'dir' ) )
			.attr( 'dir', $html.attr( 'dir' ) )
			.attr( 'lang', $html.attr( 'lang' ) )
			.append( $parsed );

		var $preview = $( '<div>' )
			.attr( 'id', 'wikiPreview' )
			.addClass( 'ontop' );

		$( '#mw-content-text' ).prepend(
			$preview.append( $note, $content )
		);

		$( 'html, body' ).animate( { scrollTop: $( '#top' ).offset().top }, 500 );
	}

	function initPreview() {
		var api = new mw.Api();
		if ( api ) {
			OO.ui.infuse( $( '#wpPreviewWidget' ) )
				.setDisabled( false );

			$( '#wpPreview' )
				.click( function ( e ) {
					e.preventDefault();

					api.parse(
						mw.libs.twoColConflict.split.merger(
							$( '.mw-twocolconflict-split-row' )
						),
						null
					).done( function ( $html ) {
						showPreview( $html );
					} );
				} );
		}
	}

	$( function () {
		initColumnSelection();
		initButtonEvents();
		initPreview();
		initTour();
	} );

}() );
