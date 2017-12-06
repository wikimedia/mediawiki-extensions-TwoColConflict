( function ( mw, $ ) {
	/**
	 * Module containing the TwoColConflict selector for the base version
	 *
	 * @constructor
	 */
	var BaseVersionSelector = function () {
		BaseVersionSelector.parent.call( this, {
			classes: [ 'mw-twocolconflict-base-dialog' ]
		} );
	};

	OO.inheritClass( BaseVersionSelector, OO.ui.Dialog );
	BaseVersionSelector.static.name = 'BaseVersionSelector';
	BaseVersionSelector.static.escapable = false;

	$.extend( BaseVersionSelector.prototype, {
		/**
		 * @type {callback}
		 */
		closeCallback: null,

		setCloseCallback: function ( callback ) {
			this.closeCallback = callback;
		},

		initialize: function () {
			BaseVersionSelector.parent.prototype.initialize.call( this );
			this.content = new OO.ui.PanelLayout( { padded: true, expanded: false } );
			this.content.$element.append(
				$( '<p>' ).text( mw.msg( 'twoColConflict-base-selection-dialog-text' ) ),
				this.composeForm()
			);
			this.$body.append( this.content.$element );
		},

		composeForm: function () {
			var self = this,
				radioSelect, submit, fieldset, form;

			radioSelect = new OO.ui.RadioSelectInputWidget( {
				name: 'mw-twocolconflict-base-version',
				classes: [ 'mw-twocolconflict-base-dialog-radio' ],
				options: [
					{
						data: 'current',
						label: mw.msg( 'twoColConflict-base-selection-foreign-label' )
					},
					{
						data: 'your',
						label: mw.msg( 'twoColConflict-base-selection-own-label' )
					}
				]
			} );

			submit = new OO.ui.ButtonWidget( {
				label: mw.msg( 'twoColConflict-base-selection-submit-label' ),
				flags: [ 'primary', 'progressive' ]
			} );
			submit.on( 'click', function () {
				self.setBaseVersion();
				self.close().closed.then(
					self.closeCallback
				);

			} );

			fieldset = new OO.ui.FieldsetLayout();
			fieldset.addItems( [
				radioSelect,
				submit
			] );

			form = new OO.ui.FormLayout( {
				items: [ fieldset ],
				action: '/api/formhandler',
				method: 'get'
			} );

			return form.$element;
		},

		setBaseVersion: function () {
			if ( $( '.mw-twocolconflict-base-dialog-radio input:checked' ).val() === 'your' ) {
				$( '#wpTextbox1' ).val( $( 'input[name="mw-twocolconflict-your-text"]' ).val() );
				mw.track( 'counter.MediaWiki.TwoColConflict.event.baseSelection.your' );
			} else {
				$( '#wpTextbox1' ).val( $( 'input[name="mw-twocolconflict-current-text"]' ).val() );
				mw.track( 'counter.MediaWiki.TwoColConflict.event.baseSelection.current' );
			}
		},

		getBodyHeight: function () {
			return this.content.$element.outerHeight( true );
		}
	} );

	mw.libs.twoColConflict = mw.libs.twoColConflict || {};
	mw.libs.twoColConflict.BaseVersionSelector = BaseVersionSelector;
}( mediaWiki, jQuery ) );
