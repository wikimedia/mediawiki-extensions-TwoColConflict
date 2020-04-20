/**
 * Draggable text item representing one of the conflicting talk page additions
 *
 * @param {Object} config Configuration options
 */
function DraggableAdditionWidget( config ) {
	// Parent constructor
	DraggableAdditionWidget.parent.call( this, config );

	if ( !config || !config.$rowElement ) {
		return;
	}

	// Mixin constructors
	OO.ui.mixin.DraggableElement.call( this, $.extend( {
		$handle: config.$rowElement.find( '.oo-ui-icon-draggable' )
	}, config ) );

	// The $element holds event listeners needed for the interaction so lets
	// append the elements from the DOM and copy the important attributes
	this.$element
		.addClass( config.$rowElement.attr( 'class' ) )
		.append( config.$rowElement.children() );
	// The original element is empty now
	config.$rowElement.remove();
}

/* Setup */
OO.inheritClass( DraggableAdditionWidget, OO.ui.Widget );
OO.mixinClass( DraggableAdditionWidget, OO.ui.mixin.DraggableElement );

module.exports = DraggableAdditionWidget;
