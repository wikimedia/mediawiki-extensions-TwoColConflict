/**
 * Module containing the SplitTwoColConflict tour
 *
 * @param {string} header for the initial dialog window
 * @param {string} image class for the initial dialog window
 * @param {string} imageHeight css value for image
 * @param {string} message for the initial dialog window
 * @param {string} close button text for the dialog window
 * @param {OO.ui.WindowManager} windowManager
 * @constructor
 */
var Tour = function ( header, image, imageHeight, message, close, windowManager ) {
	var self = this;

	function TourDialog( config ) {
		this.panel = config.panel;
		TourDialog.super.call( this, config );
	}

	OO.inheritClass( TourDialog, OO.ui.Dialog );

	TourDialog.static.name = 'TourDialog';
	TourDialog.prototype.initialize = function () {
		TourDialog.super.prototype.initialize.call( this );
		this.content = new OO.ui.PanelLayout( { padded: true, expanded: false } );
		this.content.$element.addClass( 'mw-twocolconflict-split-tour-intro-container' );
		this.content.$element.append( this.panel );
		this.$body.append( this.content.$element );
	};

	var closeButton = new OO.ui.ButtonWidget( {
		label: close,
		flags: [ 'primary', 'progressive' ]
	} );

	var $panel = $( '<div>' )
		.append(
			$( '<h5>' )
				.text( header )
				.addClass( 'mw-twocolconflict-split-tour-intro-container-header' )
		)
		.append(
			$( '<div>' )
				.addClass( 'mw-twocolconflict-split-tour-image-landscape ' + image )
				// Todo: find a better way to handle image scaling
				.css( 'height', imageHeight )
		)
		.append(
			$( '<p>' ).text( message )
		)
		.append( closeButton.$element );

	this.dialog = new TourDialog( {
		size: 'large',
		panel: $panel
	} );

	closeButton.on( 'click', function () {
		self.dialog.close();
		self.showButtons();
	} );

	this.windowManager = windowManager;
	$( 'body' ).append( this.windowManager.$element );
	this.windowManager.addWindows( [ this.dialog ] );
};

$.extend( Tour.prototype, {

	/**
	 * @type {OO.ui.Dialog}
	 */
	dialog: null,

	/**
	 * @type {OO.ui.WindowManager}
	 */
	windowManager: null,

	/**
	 * @type {Object[]}
	 */
	buttons: [],

	/**
	 * @param {jQuery} $element
	 * @return {jQuery}
	 */
	createPopupButton: function ( $element ) {
		var $pulsatingButton = $( '<div>' ).addClass( 'mw-pulsating-dot' );

		$pulsatingButton.addClass( 'mw-twocolconflict-split-tour-pulsating-button' );
		$pulsatingButton.appendTo( $element );
		$pulsatingButton.hide();

		return $pulsatingButton;
	},

	/**
	 * @param {string} header
	 * @param {string} message
	 * @param {jQuery} $pulsatingButton
	 * @return {OO.ui.PopupWidget}
	 */
	createPopup: function ( header, message, $pulsatingButton ) {
		var closeButton = new OO.ui.ButtonWidget( {
			label: mw.msg( 'twocolconflict-split-tour-popup-btn-text' ),
			flags: [ 'primary', 'progressive' ]
		} );

		var $content = $( '<div>' )
			.append( $( '<h5>' ).text( header ) )
			.append( $( '<p>' ).html( message ) );

		var popup = new OO.ui.PopupWidget( {
			position: 'above',
			align: 'forwards',
			$content: $content,
			$footer: closeButton.$element,
			padded: true,
			width: 450,
			classes: [ 'mw-twocolconflict-split-tour-popup' ]
		} );

		closeButton.on( 'click', function () {
			popup.toggle( false );
		} );

		$pulsatingButton.on( 'click', function ( e ) {
			e.preventDefault();
			$pulsatingButton.hide();
			popup.toggle( true );
		} );

		return popup;
	},

	showButtons: function () {
		var self = this;

		this.buttons.forEach( function ( data ) {
			if ( !data.popup ) {
				data.$pulsatingButton = self.createPopupButton( data.$element );
				data.popup = self.createPopup(
					data.header, data.message, data.$pulsatingButton
				);
				data.$element.append( data.popup.$element );
			}

			data.$pulsatingButton.show();
		} );
	},

	/**
	 * Adds a tutorial step to the tour, this includes a popup and a button
	 *
	 * @param {string} header for the popup
	 * @param {string} message for the popup
	 * @param {jQuery} $element to which the popup should be anchored to
	 */
	addTourPopup: function ( header, message, $element ) {
		this.buttons.push( {
			header: header,
			message: message,
			$element: $element
		} );
	},

	hideTourPopups: function () {
		this.buttons.forEach( function ( data ) {
			if ( data.popup ) {
				data.popup.toggle( false );
				data.$pulsatingButton.hide();
			}
		} );
	},

	/**
	 * @param {string[]} buttonClasses classes for the help button
	 * @return {OO.ui.ButtonWidget}
	 */
	getHelpButton: function ( buttonClasses ) {
		var self = this;

		var helpButton = new OO.ui.ButtonWidget( {
			icon: 'info',
			framed: false,
			title: mw.msg( 'twocolconflict-split-help-tooltip' ),
			classes: buttonClasses
		} );

		helpButton.on( 'click', function () {
			self.showTour();
		} );

		return helpButton.$element;
	},

	showTour: function () {
		this.hideTourPopups();
		this.windowManager.openWindow( this.dialog );
	}
} );

function isSingleColumnView() {
	return $( 'input[name="mw-twocolconflict-single-column-view"]' ).val() === '1';
}

/**
 * Initializes the tour
 */
function initialize() {
	var $body = $( 'body' ),
		hideDialogSetting,
		Settings = require( '../ext.TwoColConflict.Settings.js' ),
		settings = new Settings(),
		tour,
		windowManager = new OO.ui.WindowManager();

	if ( isSingleColumnView() ) {
		hideDialogSetting = 'hide-help-dialogue-single-column-view';

		tour = new Tour(
			mw.msg( 'twocolconflict-split-tour-dialog-header-single-column-view' ),
			'mw-twocolconflict-split-tour-slide-single-column-view-1',
			'240px',
			mw.msg( 'twocolconflict-split-tour-dialog-message-single-column-view' ),
			mw.msg( 'twocolconflict-split-tour-dialog-btn-text-single-column-view' ),
			windowManager
		);

		$( '.firstHeading' ).append(
			tour.getHelpButton( [ 'mw-twocolconflict-split-tour-help-button-single-column-view' ] )
		);
	} else {
		hideDialogSetting = 'hide-help-dialogue';

		tour = new Tour(
			mw.msg( 'twocolconflict-split-tour-dialog-header' ),
			'mw-twocolconflict-split-tour-slide-dual-column-view-1',
			'180px',
			mw.msg( 'twocolconflict-split-tour-dialog-message' ),
			mw.msg( 'twocolconflict-split-tour-dialog-btn-text' ),
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

		$( '.mw-twocolconflict-split-flex-header' ).append(
			tour.getHelpButton( [ 'mw-twocolconflict-split-tour-help-button' ] )
		);
	}

	if ( !settings.loadBoolean( hideDialogSetting, false ) ) {
		tour.showTour();
		settings.saveBoolean( hideDialogSetting, true );
	}
}

module.exports = initialize;
