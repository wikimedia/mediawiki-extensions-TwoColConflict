( function ( mw ) {
	var helpDialog = mw.libs.twoColConflict.HelpDialog,
		dialog;

	QUnit.module( 'ext.TwoColConflict.HelpDialog' );

	QUnit.test( 'Initialize HelpDialog', function ( assert ) {
		helpDialog.init( {
			name: 'twocolconflict',
			title: 'twocolconflict-tutorial',
			size: 'medium',
			prev: 'twocolconflict-previous-dialog',
			next: 'twocolconflict-next-dialog',
			close: 'twocolconflict-close-dialog',
			slides: [
				{
					message: 'twocolconflict-help-dialog-slide1',
					parameters: [],
					imageClass: 'mw-twocolconflict-help-dialog-slide-1',
					imageMode: 'landscape'
				},
				{
					message: 'twocolconflict-help-dialog-slide2',
					parameters: [],
					imageClass: 'mw-twocolconflict-help-dialog-slide-2',
					imageMode: 'landscape'
				},
				{
					message: 'twocolconflict-help-dialog-slide3',
					parameters: [],
					imageClass: 'mw-twocolconflict-help-dialog-slide-3',
					imageMode: 'landscape'
				},
				{
					message: 'twocolconflict-help-dialog-slide4',
					parameters: [],
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
			addLinkTargets( mw.message( 'twocolconflict-help-dialog-slide1' ).parse() )
		);
		assert.equal(
			getSlideTextHtml( dialog.slides[ 1 ] ),
			addLinkTargets( mw.message( 'twocolconflict-help-dialog-slide2' ).parse() )
		);
		assert.equal(
			getSlideTextHtml( dialog.slides[ 2 ] ),
			addLinkTargets( mw.message( 'twocolconflict-help-dialog-slide3' ).parse() )
		);
		assert.equal(
			getSlideTextHtml( dialog.slides[ 3 ] ),
			addLinkTargets( mw.message( 'twocolconflict-help-dialog-slide4' ).parse() )
		);
		assert.equal( dialog.getCssPrefix(), 'mw-twocolconflict' );
		assert.ok( dialog.slides[ 0 ].$element.find( 'div' ).hasClass( 'mw-twocolconflict-help-dialog-slide-1' ) );
		assert.ok( dialog.slides[ 1 ].$element.find( 'div' ).hasClass( 'mw-twocolconflict-help-dialog-slide-2' ) );
		assert.ok( dialog.slides[ 2 ].$element.find( 'div' ).hasClass( 'mw-twocolconflict-help-dialog-slide-3' ) );
		assert.ok( dialog.slides[ 3 ].$element.find( 'div' ).hasClass( 'mw-twocolconflict-help-dialog-slide-4' ) );
	} );

}( mediaWiki ) );
