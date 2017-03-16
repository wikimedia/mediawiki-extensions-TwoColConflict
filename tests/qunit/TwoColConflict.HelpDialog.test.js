( function ( mw ) {
	var helpDialog = mw.libs.twoColConflict.HelpDialog,
		dialog;

	QUnit.module( 'ext.TwoColConflict.HelpDialog' );

	QUnit.test( 'Initialize HelpDialog', function ( assert ) {
		helpDialog.init( {
			name: 'twoColConflict',
			title: 'twoColConflict-tutorial',
			size: 'medium',
			prev: 'twoColConflict-previous-dialog',
			next: 'twoColConflict-next-dialog',
			close: 'twoColConflict-close-dialog',
			slides: [
				{
					message: 'twoColConflict-help-dialog-slide1',
					imageClass: 'mw-twocolconflict-help-dialog-slide-1',
					imageMode: 'landscape'
				},
				{
					message: 'twoColConflict-help-dialog-slide2',
					imageClass: 'mw-twocolconflict-help-dialog-slide-2',
					imageMode: 'landscape'
				},
				{
					message: 'twoColConflict-help-dialog-slide3',
					imageClass: 'mw-twocolconflict-help-dialog-slide-3',
					imageMode: 'landscape'
				},
				{
					message: 'twoColConflict-help-dialog-slide4',
					imageClass: 'mw-twocolconflict-help-dialog-slide-4',
					imageMode: 'landscape'
				}
			]
		} );

		dialog = helpDialog.getDialogInstance();

		helpDialog.show();
		helpDialog.hide();

		function getSlideTextHtml( slide ) {
			return slide.$element.find( '.mw-twocolconflict-help-dialog-text' ).html();
		}

		function addLinkTargets( parsedMessage ) {
			var $container = $( '<div>' ).html( parsedMessage );
			$container.find( 'a' ).attr( 'target', '_blank' );
			return $container.html();
		}

		assert.equal( dialog.slides.length, 4 );
		assert.equal( dialog.slidePointer, 0 );
		assert.equal(
			getSlideTextHtml( dialog.slides[ 0 ] ),
			addLinkTargets( mw.message( 'twoColConflict-help-dialog-slide1' ).parse() )
		);
		assert.equal(
			getSlideTextHtml( dialog.slides[ 1 ] ),
			addLinkTargets( mw.message( 'twoColConflict-help-dialog-slide2' ).parse() )
		);
		assert.equal(
			getSlideTextHtml( dialog.slides[ 2 ] ),
			addLinkTargets( mw.message( 'twoColConflict-help-dialog-slide3' ).parse() )
		);
		assert.equal(
			getSlideTextHtml( dialog.slides[ 3 ] ),
			addLinkTargets( mw.message( 'twoColConflict-help-dialog-slide4' ).parse() )
		);
		assert.equal( dialog.getCssPrefix(), 'mw-twocolconflict' );
		assert.ok( dialog.slides[ 0 ].$element.find( 'div' ).hasClass( 'mw-twocolconflict-help-dialog-slide-1' ) );
		assert.ok( dialog.slides[ 1 ].$element.find( 'div' ).hasClass( 'mw-twocolconflict-help-dialog-slide-2' ) );
		assert.ok( dialog.slides[ 2 ].$element.find( 'div' ).hasClass( 'mw-twocolconflict-help-dialog-slide-3' ) );
		assert.ok( dialog.slides[ 3 ].$element.find( 'div' ).hasClass( 'mw-twocolconflict-help-dialog-slide-4' ) );
	} );

}( mediaWiki ) );
