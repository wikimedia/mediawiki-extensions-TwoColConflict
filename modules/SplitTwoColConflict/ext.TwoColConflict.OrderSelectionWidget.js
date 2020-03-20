/**
 * Draggable group widget containing the conflicting talk page additions
 *
 * @param {Object} [config] Configuration options
 */
function OrderSelectionWidget( config ) {
	// Configuration initialization
	config = config || {};
	// Parent constructor
	OrderSelectionWidget.parent.call( this, config );
	// Mixin constructors
	OO.ui.mixin.DraggableGroupElement.call( this, $.extend( {
		$group: this.$element
	}, config ) );

	this.connect( this, {
		reorder: 'onReorder'
	} );
}

/* Setup */
OO.inheritClass( OrderSelectionWidget, OO.ui.Widget );
OO.mixinClass( OrderSelectionWidget, OO.ui.mixin.DraggableGroupElement );

/**
 * Respond to the selection widget reorder event
 *
 * FIXME: This is gross how I've coupled specifics about diff types into the reorder handler.
 *
 * @param {OO.ui.mixin.DraggableElement} item
 */
OrderSelectionWidget.prototype.onReorder = function ( item ) {
	var otherRowNum, yourRowNum,
		$group = item.$element.closest( '.oo-ui-draggableGroupElement' ),
		$other = $group.find( '.mw-twocolconflict-split-delete' ),
		$your = $group.find( '.mw-twocolconflict-split-add' );

	if ( $other.length !== 1 || $your.length !== 1 ) {
		return;
	}

	function getRowNumber( $column ) {
		var name = $column.find( 'textarea[name^="mw-twocolconflict-split-content"]' ).attr( 'name' );
		return name.match( /\d+/ )[ 0 ];
	}

	function setRowNumber( $column, oldRowNum, newRowNum ) {
		$column.find( 'input, textarea' ).each( function ( index, input ) {
			var $input = $( input ),
				name = $input.attr( 'name' );
			$input.attr( 'name', name.replace( '[' + oldRowNum + ']', '[' + newRowNum + ']' ) );
		} );
	}

	otherRowNum = getRowNumber( $other );
	yourRowNum = getRowNumber( $your );
	setRowNumber( $other, otherRowNum, yourRowNum );
	setRowNumber( $your, yourRowNum, otherRowNum );
};

module.exports = OrderSelectionWidget;
