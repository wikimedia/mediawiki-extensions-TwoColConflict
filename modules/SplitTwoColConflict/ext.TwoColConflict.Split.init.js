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
	 * @param {jQuery} $column
	 */
	function disableColumn( $column ) {
		getColumnEditButton( $column )
			.setDisabled( true )
			.setTitle( mw.msg( 'twocolconflict-split-disabled-edit-tooltip' ) );
		$column
			.removeClass( 'mw-twocolconflict-split-selected' )
			.addClass( 'mw-twocolconflict-split-unselected' );
	}

	/**
	 * @param {jQuery} $column
	 */
	function enableColumn( $column ) {
		getColumnEditButton( $column )
			.setDisabled( false )
			.setTitle( mw.msg( 'twocolconflict-split-edit-tooltip' ) );
		$column
			.addClass( 'mw-twocolconflict-split-selected' )
			.removeClass( 'mw-twocolconflict-split-unselected' );
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
			$editor = $selected.find( '.mw-twocolconflict-split-editor' ),
			$resetEditorText = $selected.find( '.mw-twocolconflict-split-reset-editor-text' ),
			$diffText = $selected.find( '.mw-twocolconflict-split-difftext' );

		if ( $editor.val() === $resetEditorText.text() ) {
			var $resetDiffText = $selected.find( '.mw-twocolconflict-split-reset-diff-text' );
			$diffText.html( $resetDiffText.html() );
		} else {
			$diffText.text( $editor.val() );
		}

		disableEditing( $row );
	}

	/**
	 * @param {jQuery} $row
	 */
	function resetWarning( $row ) {
		var $selected = $row.find( '.mw-twocolconflict-split-selected, .mw-twocolconflict-split-copy' ),
			$editor = $selected.find( '.mw-twocolconflict-split-editor' ),
			$resetEditorText = $selected.find( '.mw-twocolconflict-split-reset-editor-text' );

		if ( $editor.val() === $resetEditorText.text() ) {
			disableEditing( $row );
			return;
		}

		OO.ui.confirm(
			mw.msg( 'twocolconflict-split-reset-warning' ),
			{
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
				var $diffText = $selected.find( '.mw-twocolconflict-split-difftext' ),
					$resetDiffText = $selected.find( '.mw-twocolconflict-split-reset-diff-text' );

				$editor.val( $resetEditorText.text() );
				$diffText.html( $resetDiffText.html() );
				disableEditing( $row );
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
			} );
		} );
	}

	function handleSelectColumn() {
		var $group = $( this ).closest( '.mw-twocolconflict-split-selection' ),
			$checked = $group.find( 'input:checked' ),
			$row = $group.closest( '.mw-twocolconflict-split-row' ),
			$label = $row.find( '.mw-twocolconflict-split-selector-label span' ),
			$selection = $row.find( '.mw-twocolconflict-split-selection' ),
			// TODO: Rename classes, "add" should be "your", etc.
			$yourColumn = $row.find( '.mw-twocolconflict-split-add' ),
			$otherColumn = $row.find( '.mw-twocolconflict-split-delete' );

		$selection.find( '.oo-ui-inputWidget-input' ).each( function () {
			$( this ).prop( 'title', mw.msg(
				( $( this ).is( ':checked' ) ) ? 'twocolconflict-split-selected-version-tooltip' :
					'twocolconflict-split-unselected-version-tooltip'
			) );
		} );

		if ( $checked.val() === 'your' ) {
			disableColumn( $otherColumn );
			enableColumn( $yourColumn );
			$row.removeClass( 'mw-twocolconflict-no-selection' );
			$label.text( mw.msg( 'twocolconflict-split-your-version-chosen' ) );
		} else if ( $checked.val() === 'other' ) {
			enableColumn( $otherColumn );
			disableColumn( $yourColumn );
			$row.removeClass( 'mw-twocolconflict-no-selection' );
			$label.text( mw.msg( 'twocolconflict-split-other-version-chosen' ) );
		} else {
			disableColumn( $otherColumn );
			disableColumn( $yourColumn );
			$label.text( mw.msg( 'twocolconflict-split-choose-version' ) );
		}
	}

	function initColumnSelection() {
		var $switches = $( '.mw-twocolconflict-split-selection' ),
			$radioButtons = $switches.find( 'input' );

		// TODO remove when having no selection is the default
		$radioButtons.prop( 'checked', false );
		$radioButtons.on( 'change', handleSelectColumn );

		$switches.find( 'input:first-of-type' ).trigger( 'change' );
	}

	function initTour() {
		var $body = $( 'body' ),
			Settings = require( '../ext.TwoColConflict.Settings.js' ),
			Tour = require( 'ext.TwoColConflict.Split.Tour' ),
			settings = new Settings(),
			windowManager = new OO.ui.WindowManager();

		var tour = Tour.init(
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

	function showPreview( parsedContent, parsedNote ) {
		$( '#wikiPreview' ).remove();
		var $html = $( 'html' );

		var $note = $( '<div>' )
			.addClass( 'previewnote' )
			.append(
				$( '<h2>' )
					.attr( 'id', 'mw-previewheader' )
					.append( mw.msg( 'preview' ) ),
				$( '<div>' )
					.addClass( 'warningbox' )
					.append( $( parsedNote ).children() )
			);

		var $content = $( '<div>' )
			.addClass( 'mw-content-' + $html.attr( 'dir' ) )
			.attr( 'dir', $html.attr( 'dir' ) )
			.attr( 'lang', $html.attr( 'lang' ) )
			.append( parsedContent );

		var $preview = $( '<div>' )
			.attr( 'id', 'wikiPreview' )
			.addClass( 'ontop' );

		$( '#mw-content-text' ).prepend(
			$preview.append( $note, $content )
		);

		$( 'html, body' ).animate( { scrollTop: $( '#top' ).offset().top }, 500 );
	}

	function validateForm() {
		var isFormValid = true;

		$( '.mw-twocolconflict-split-selection' ).each( function () {
			var $row = $( this ).closest( '.mw-twocolconflict-split-row' ),
				$checked = $row.find( 'input:checked' );

			if ( $checked.length ) {
				$row.removeClass( 'mw-twocolconflict-no-selection' );
			} else {
				$row.addClass( 'mw-twocolconflict-no-selection' );
				isFormValid = false;
			}
		} );

		return isFormValid;
	}

	function initPreview() {
		var merger = require( 'ext.TwoColConflict.Split.Merger' ),
			api = new mw.Api(),
			$previewBtn = $( '#wpPreviewWidget' );
		if ( api && $previewBtn.length ) {
			OO.ui.infuse( $previewBtn )
				.setDisabled( false );

			$( '#wpPreview' )
				.click( function ( e ) {
					e.preventDefault();

					if ( !validateForm() ) {
						return;
					}

					var arrow = $( 'html' ).attr( 'dir' ) === 'rtl' ? '←' : '→',
						title = mw.config.get( 'wgPageName' );

					$.when(
						api.parse(
							merger( $( '.mw-twocolconflict-split-row' ) ),
							{
								title: title,
								prop: 'text',
								pst: true,
								disablelimitreport: true,
								disableeditsection: true
							}
						),
						api.parse(
							'{{int:previewnote}} <span class="mw-continue-editing">[[#editform|' +
								arrow + ' {{int:continue-editing}}]]</span>',
							{
								title: title,
								prop: 'text',
								disablelimitreport: true,
								disableeditsection: true
							}
						)
					).done( function ( parsedContent, parsedNote ) {
						showPreview( parsedContent, parsedNote );
					} );
				} );
		}
	}

	function initSubmit() {
		$( '#wpSave, #wpTestPreviewWidget #wpPreview' )
			.click( function ( e ) {
				if ( !validateForm() ) {
					e.preventDefault();
				}
			} );
	}

	$( function () {
		var initTracking = require( './ext.TwoColConflict.Split.tracking.js' );

		// disable all javascript from this feature when testing the nojs implementation
		if ( mw.cookie.get( '-twocolconflict-test-nojs', 'mw' ) ) {
			// set CSS class so nojs CSS rules are applied
			$( 'html' ).removeClass( 'client-js' ).addClass( 'client-nojs' );
			return;
		}
		initColumnSelection();
		initButtonEvents();
		initPreview();
		initSubmit();
		initTour();
		initTracking();
	} );
}() );
