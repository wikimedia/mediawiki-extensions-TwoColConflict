( function ( $ ) {

	/**
	 * Calculate the top offset of two elements
	 *
	 * @param {jQuery} $source
	 * @param {jQuery} $target
	 * @return {number}
	 */
	function getDivTopOffset( $source, $target ) {
		return $target.offset().top - $source.offset().top;
	}

	/**
	 * Synchronize width and height of the hidden textbox with the
	 * actual textbox
	 */
	function synchronizeHiddenTextBox() {
		var $hiddenChangesEditor = $( '.mw-twocolconflict-hidden-editor' ),
			$textEditor = $( '#wpTextbox1' );

		$hiddenChangesEditor.width(
			$textEditor.width()
		);
		$hiddenChangesEditor.height(
			$textEditor.height()
		);
	}

	/**
	 * Calculate and set data of relative top positions for change div elements
	 * with the help of the plain marked up div elements in the hidden textbox
	 */
	function setScrollBaseData() {
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

		synchronizeHiddenTextBox();

		for ( count = $changeDivs.length; i < count; i++ ) {
			$currentDiv = $( $changeDivs[ i ] );

			if ( $currentDiv.hasClass( 'mw-twocolconflict-diffchange-own' ) &&
				$currentDiv.hasClass( 'mw-twocolconflict-diffchange-conflict' ) ) {
				offset = getDivTopOffset( $hiddenEditor, $( $plainChangeDivs[ i - 1 ] ) );
			} else {
				offset = getDivTopOffset( $hiddenEditor, $( $plainChangeDivs[ i ] ) );
			}
			$currentDiv.attr( 'data-scroll-base', offset );
		}
	}

	/**
	 * Scroll the conflict- and editor-view to the position of the given
	 * change div element
	 *
	 * @param {jQuery} $changeDiv
	 */
	function scrollToConflictWithData( $changeDiv ) {
		var $changesEditor = $( '.mw-twocolconflict-changes-editor' ),
			$textEditor = $( '#wpTextbox1' ),
			changeDivOffset, dataOffset;

		dataOffset = parseInt( $changeDiv.attr( 'data-scroll-base' ) );

		changeDivOffset = getDivTopOffset(
			$changesEditor,
			$changeDiv
		);

		$changesEditor.animate( {
			scrollTop: changeDivOffset + $changesEditor.scrollTop(),
			duration: 1000
		} );

		$textEditor.animate( {
			scrollTop: dataOffset,
			duration: 1000
		} );
	}

	function scrollToFirstConflict() {
		scrollToConflictWithData(
			$( '.mw-twocolconflict-diffchange-conflict:eq(0)' )
		);
	}

	$( function () {
		setScrollBaseData();
		scrollToFirstConflict();

		$( window ).on( 'resize', function() {
			setScrollBaseData();
		} );

		$(
			'.mw-twocolconflict-diffchange-foreign, ' +
			'.mw-twocolconflict-diffchange-own'
		).click( function() {
			scrollToConflictWithData( $( this ) );
		} );
	} );
}( jQuery ) );
