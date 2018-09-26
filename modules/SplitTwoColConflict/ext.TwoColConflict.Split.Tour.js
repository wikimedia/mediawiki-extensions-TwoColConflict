( function ( mw, $ ) {

	/**
	 * Module containing the SplitTwoColConflict tour
	 *
	 * @param {string} header for the initial dialog window
	 * @param {string} image class for the initial dialog window
	 * @param {string} message for the initial dialog window
	 * @constructor
	 */
	var Tour = function ( header, image, message ) {
		var $closeButton,
			$panel,
			self = this;

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

		$closeButton = new OO.ui.ButtonWidget( {
			label: mw.msg( 'twocolconflict-split-tour-dialog-btn-text' ),
			flags: [ 'primary', 'progressive' ]
		} );

		$panel = $( '<div>' )
			.append(
				$( '<h5>' )
					.text( header )
					.addClass( 'mw-twocolconflict-split-tour-intro-container-header' )
			)
			.append(
				$( '<div>' ).addClass(
					'mw-twocolconflict-split-tour-image-landscape ' + image
				)
			)
			.append(
				$( '<p>' ).text( message )
			)
			.append( $closeButton.$element );

		this.$dialog = new TourDialog( {
			size: 'large',
			panel: $panel
		} );

		$closeButton.on( 'click', function () {
			self.$dialog.close();
			self.showButtons();
		} );
	};

	$.extend( Tour.prototype, {

		/**
		 * @type {TourDialog}
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
			var $stillButton = $( '<div>' ),
				$pulsatingButton = $( '<div>' ),
				center = $element.position().left + $element.outerWidth() / 2;

			$pulsatingButton.addClass( 'mw-twocolconflict-split-tour-pulsating-button' );
			$stillButton.addClass( 'mw-twocolconflict-split-tour-still-button' );
			$stillButton.css( 'left', center );
			$stillButton.appendTo( $element );
			$stillButton.hide();

			$pulsatingButton.appendTo( $stillButton );
			return $stillButton;
		},

		/**
		 * @param {String} header
		 * @param {String} message
		 * @param {jQuery} $pulsatingButton
		 * @return {OO.ui.PopupWidget}
		 */
		createPopup: function ( header, message, $pulsatingButton ) {
			var $closeButton, $content, $popup;

			$closeButton = new OO.ui.ButtonWidget( {
				label: mw.msg( 'twocolconflict-split-tour-popup-btn-text' ),
				flags: [ 'primary', 'progressive' ]
			} );

			$content = $( '<div>' )
				.append( $( '<h5>' ).text( header ) )
				.append( $( '<p>' ).html( message ) )
				.append( $closeButton.$element );

			$popup = new OO.ui.PopupWidget( {
				$content: $content,
				padded: true,
				width: 450,
				classes: [ 'mw-twocolconflict-split-tour-popup' ]
			} );

			$closeButton.on( 'click', function () {
				$popup.toggle( false );
			} );

			$pulsatingButton.on( 'click', function ( e ) {
				e.preventDefault();
				$pulsatingButton.hide();
				$popup.toggle( true );
			} );

			return $popup;
		},

		showButtons: function () {
			var self = this;

			this.buttons.forEach( function ( data ) {
				var $pulsatingButton = self.createPopupButton( data.$element ),
					$popup = self.createPopup( data.header, data.message, $pulsatingButton );

				data.$element.append( $popup.$element );
				$pulsatingButton.show();
			} );

			this.buttons = [];
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

		/**
		 * @return {OO.ui.ButtonWidget}
		 */
		getHelpButton: function () {
			var $helpButton,
				self = this;

			$helpButton = new OO.ui.ButtonWidget( {
				icon: 'info',
				framed: false,
				classes: [ 'mw-twocolconflict-split-tour-help-button' ]
			} );

			$helpButton.on( 'click', function () {
				self.showTour();
			} );

			return $helpButton.$element;
		},

		showTour: function () {
			if ( !this.windowManager ) {
				this.windowManager = new OO.ui.WindowManager();
				$( 'body' ).append( this.windowManager.$element );
				this.windowManager.addWindows( [ this.$dialog ] );
			}
			this.windowManager.openWindow( this.$dialog );
		}
	} );

	/**
	 * Initializes the tour
	 *
	 * @param {string} header for the initial dialog window
	 * @param {string} image class for the initial dialog window
	 * @param {string} message for the initial dialog window
	 * @return {Tour}
	 */
	Tour.init = function ( header, image, message ) {
		return new Tour( header, image, message );
	};

	mw.libs.twoColConflict = mw.libs.twoColConflict || {};
	mw.libs.twoColConflict.split = mw.libs.twoColConflict.split || {};
	mw.libs.twoColConflict.split.Tour = Tour;
}( mediaWiki, jQuery ) );
