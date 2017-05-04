( function ( mw, $ ) {

	/**
	 * @constructor
	 */
	var AutoScroll = function () {
	};

	$.extend( AutoScroll.prototype, {
		/**
		 * Calculate the top offset of two elements
		 *
		 * @param {jQuery} $source
		 * @param {jQuery} $target
		 * @return {number}
		 */
		getDivTopOffset: function ( $source, $target ) {
			return $target.offset().top - $source.offset().top;
		},

		/**
		 * Get the height of the change div titles
		 *
		 * @return {number}
		 */
		getChangeDivTitleHeight: function () {
			return parseInt( $( '.mw-twocolconflict-diffchange-title:first' ).outerHeight( true ) );
		},

		/**
		 * Synchronize width and height of the hidden textbox with the
		 * actual textbox
		 */
		synchronizeHiddenTextBox: function () {
			var $hiddenChangesEditor = $( '.mw-twocolconflict-hidden-editor' ),
				$textEditor = $( '#wpTextbox1' );

			$hiddenChangesEditor.width(
				$textEditor.width()
			);
			$hiddenChangesEditor.height(
				$textEditor.height()
			);
		},

		/**
		 * Calculate and set data of relative top positions for change div elements
		 * with the help of the plain marked up div elements in the hidden textbox
		 */
		setScrollBaseData: function () {
			var $changeDivs = $(
					'.mw-twocolconflict-diffchange-own, ' +
					'.mw-twocolconflict-diffchange-foreign, ' +
					'.mw-twocolconflict-diffchange-same, ' +
					'.mw-twocolconflict-diffchange-conflict'
				),
				$plainChangeDivs = $(
					'.mw-twocolconflict-plain-own, ' +
					'.mw-twocolconflict-plain-foreign, ' +
					'.mw-twocolconflict-plain-same, ' +
					'.mw-twocolconflict-plain-conflict'
				),
				$hiddenEditor = $( '.mw-twocolconflict-hidden-editor' ),
				i = 0, count, $currentDiv, offset;

			this.synchronizeHiddenTextBox();

			for ( count = $changeDivs.length; i < count; i++ ) {
				$currentDiv = $( $changeDivs[ i ] );

				if ( $currentDiv.hasClass( 'mw-twocolconflict-diffchange-own' ) &&
					$currentDiv.hasClass( 'mw-twocolconflict-diffchange-conflict' ) ) {
					offset = this.getDivTopOffset( $hiddenEditor, $( $plainChangeDivs[ i - 1 ] ) );
				} else {
					offset = this.getDivTopOffset( $hiddenEditor, $( $plainChangeDivs[ i ] ) );
				}
				$currentDiv.attr( 'data-scroll-base', offset );
			}
		},

		/**
		 * Check if a change div element upper boundary is in view of its scrollable
		 *
		 * @param {jQuery} $changeDiv
		 * @param {jQuery} $scrollable
		 * @return {boolean}
		 */
		changeElementTopIsInView: function ( $changeDiv, $scrollable ) {
			return this.getDivTopOffset( $changeDiv, $scrollable ) < 0;
		},

		/**
		 * Get the first visible change div in the conflict-view
		 *
		 * @return {jQuery}
		 */
		getFirstVisibleChangesElement: function () {
			var $changesEditor = $( '.mw-twocolconflict-changes-editor' ),
				$changeDivs = $(
					'.mw-twocolconflict-diffchange-own, ' +
					'.mw-twocolconflict-diffchange-foreign, ' +
					'.mw-twocolconflict-diffchange-same, ' +
					'.mw-twocolconflict-diffchange-conflict'
				),
				i = 0, count, $currentDiv;

			for ( count = $changeDivs.length; i < count; i++ ) {
				$currentDiv = $( $changeDivs[ i ] );

				if ( this.changeElementTopIsInView( $currentDiv, $changesEditor ) ) {
					break;
				}
			}

			return $currentDiv;
		},

		/**
		 * Scroll the conflict-view to the position of the given change div element
		 * with an additional offset
		 *
		 * @param {jQuery} $changeDiv
		 * @param {number} manualOffset
		 */
		scrollToChangeWithOffset: function ( $changeDiv, manualOffset ) {
			var $changesEditor = $( '.mw-twocolconflict-changes-editor' ),
				changeDivOffset;

			changeDivOffset = this.getDivTopOffset(
				$changesEditor,
				$changeDiv
			);

			$changesEditor.scrollTop( changeDivOffset + $changesEditor.scrollTop() + manualOffset );
		},

		/**
		 * Scroll the conflict- and editor-view to the position of the given
		 * change div element
		 *
		 * @param {jQuery} $changeDiv
		 */
		scrollToConflictWithData: function ( $changeDiv ) {
			var $changesEditor = $( '.mw-twocolconflict-changes-editor' ),
				$textEditor = $( '#wpTextbox1' ),
				changeDivOffset, dataOffset;

			dataOffset = parseInt( $changeDiv.attr( 'data-scroll-base' ) );

			changeDivOffset = this.getDivTopOffset(
				$changesEditor,
				$changeDiv
			);

			$changesEditor.animate( {
				scrollTop: changeDivOffset + $changesEditor.scrollTop(),
				duration: 1000
			} );

			$textEditor.animate( {
				scrollTop: dataOffset - this.getChangeDivTitleHeight(),
				duration: 1000
			} );
		},

		/**
		 * Scroll to the first conflict
		 */
		scrollToFirstOwnOrConflict: function () {
			this.scrollToConflictWithData(
				$( $(
					'.mw-twocolconflict-diffchange-conflict, .mw-twocolconflict-diffchange-own'
				)[ 0 ] )
			);
		}
	} );

	mw.libs.twoColConflict = mw.libs.twoColConflict || {};
	mw.libs.twoColConflict.AutoScroll = AutoScroll;
}( mediaWiki, jQuery ) );
