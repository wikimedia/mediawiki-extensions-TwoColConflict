( function ( mw, $ ) {
	/**
	 * Module containing the TwoColConflict tutorial
	 *
	 * @param {Object} config
	 * @constructor
	 */
	var HelpDialog = function ( config ) {
		this.config = config;
		HelpDialog.super.call( this, { size: config.size, classes: [ this.getCssPrefix() + '-help-dialog' ] } );
	};

	OO.inheritClass( HelpDialog, OO.ui.ProcessDialog );

	$.extend( HelpDialog.prototype, {
		/**
		 * @type {Object}
		 */
		config: [],

		/**
		 * @type {OO.ui.PanelLayout[]}
		 */
		slides: [],

		/**
		 * @type {number}
		 */
		slidePointer: 0,

		initialize: function () {
			var i;

			HelpDialog.super.prototype.initialize.call( this );

			for ( i = 0; i < this.config.slides.length; i++ ) {
				this.slides.push(
					this.getSlide(
						this.config.slides[ i ].message,
						this.config.slides[ i ].imageClass,
						this.config.slides[ i ].imageMode
					)
				);
			}

			this.stackLayout = new OO.ui.StackLayout( {
				items: this.slides
			} );

			this.$body.append( this.stackLayout.$element );
		},

		/**
		 * @return {string}
		 */
		getCssPrefix: function () {
			return this.config.name.toLowerCase();
		},

		/**
		 * @param {string} message to be parse by mw.message
		 * @param {string} imageClass class of image to show
		 * @param {string} imageMode how the image should be displayed, possible values:
		 * - "landscape"
		 * - "portrait"
		 * @return {OO.ui.PanelLayout}
		 */
		getSlide: function ( message, imageClass, imageMode ) {
			var slide = new OO.ui.PanelLayout( { $: this.$, padded: true, expanded: false } );

			slide.$element
				.append(
					$( '<div>' ).addClass(
						'mw-' + this.getCssPrefix() +
						'-help-dialog-image-' + imageMode + ' ' + imageClass
					)
				)
				.append(
					$( '<p>' ).addClass( 'mw-' + this.getCssPrefix() + '-help-dialog-text' )
						.html( mw.message( message ).parse() )
				);

			slide.$element.find( 'a' ).attr( 'target', '_blank' );

			return slide;
		},

		/**
		 * @param {string} action
		 * @return {OO.ui.Process}
		 */
		getActionProcess: function ( action ) {
			if ( action === 'next' ) {
				this.stackLayout.setItem( this.slides[ ++this.slidePointer ] );
			} else if ( action === 'previous' ) {
				this.stackLayout.setItem( this.slides[ --this.slidePointer ] );
			}

			if ( this.slidePointer === 0 ) {
				this.actions.setMode( 'initial' );
			} else if ( this.slidePointer === this.slides.length - 1 ) {
				this.actions.setMode( 'last' );
			} else {
				this.actions.setMode( 'middle' );
			}

			this.stackLayout.$element.closest( '.oo-ui-window-frame' ).css( 'height', this.getContentHeight() + 'px' );
			return HelpDialog.super.prototype.getActionProcess.call( this, action );
		},

		getSetupProcess: function ( data ) {
			return HelpDialog.super.prototype.getSetupProcess.call( this, data )
				.next( function () {
					this.actions.setMode( 'initial' );
				}, this );
		},

		/**
		 * Needed to set the initial height of the dialog
		 *
		 * @return {number}
		 */
		getBodyHeight: function () {
			return this.slides[ this.slidePointer ].$element.outerHeight( true );
		}
	} );

	/**
	 * Initializes the help dialog
	 * @param {Object} config
	 */
	HelpDialog.init = function ( config ) {
		var windowManager = new OO.ui.WindowManager(),
			dialog;

		$( 'body' )
			.append( windowManager.$element )
			.click( function ( event ) {
				if ( $( event.target ).hasClass( config.name.toLowerCase() + '-help-dialog' ) ) {
					HelpDialog.hide();
				}
			} );

		HelpDialog.static.title = mw.msg( config.title );
		HelpDialog.static.name = config.title;
		HelpDialog.static.actions = [
			{
				action: 'next',
				label: mw.msg( config.next ),
				flags: [ 'primary', 'progressive' ],
				modes: [ 'initial', 'middle' ],
				classes: [ config.name.toLowerCase() + '-help-next' ]
			},
			{ action: 'previous', flags: 'safe', label: mw.msg( config.prev ), modes: [ 'middle', 'last' ], classes: [ config.name + '-help-previous' ] },
			{ label: mw.msg( config.close ), flags: 'safe', modes: 'initial', classes: [ config.name.toLowerCase() + '-help-close-start' ] },
			{ label: mw.msg( config.close ), flags: 'primary', modes: 'last', classes: [ config.name.toLowerCase() + '-help-close-end' ] }
		];

		dialog = new HelpDialog( config );

		HelpDialog.show = function () {
			if ( !windowManager.hasWindow( dialog ) ) {
				windowManager.addWindows( [ dialog ] );
			}

			dialog.slidePointer = 0;
			dialog.stackLayout.setItem( dialog.slides[ dialog.slidePointer ] );

			windowManager.openWindow( dialog );
		};

		HelpDialog.hide = function () {
			if ( windowManager.hasWindow( dialog ) ) {
				windowManager.closeWindow( dialog );
			}
		};

		HelpDialog.getDialogInstance = function () {
			return dialog;
		};
	};

	mw.libs.twoColConflict = mw.libs.twoColConflict || {};
	mw.libs.twoColConflict.HelpDialog = HelpDialog;
}( mediaWiki, jQuery ) );
