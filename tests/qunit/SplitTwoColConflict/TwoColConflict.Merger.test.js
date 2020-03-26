( function () {
	function buildColumn( params ) {
		return $( '<div>' )
			.addClass( 'mw-twocolconflict-split-column' )
			.append(
				$( '<textarea>' )
					.addClass( 'mw-twocolconflict-split-editor' )
					.append( params.content )
			)
			.append(
				$( '<input>' )
					.attr( 'name', 'mw-twocolconflict-split-linefeeds' )
					.val( params.linefeeds )
			);
	}

	function buildColumns( paramArray ) {
		return paramArray.map( buildColumn ).reduce( $.merge );
	}

	QUnit.module( 'ext.TwoColConflict.Split.Merger' );
	var merger = require( 'ext.TwoColConflict.Split.Merger' );

	QUnit.test( 'testSingleCopyRow', function ( assert ) {
		assert.strictEqual(
			merger(
				buildColumns( [
					{ content: 'A', linefeeds: 0 }
				] )
			),
			'A'
		);
	} );

	QUnit.test( 'testMultipleColumns', function ( assert ) {
		assert.strictEqual(
			merger(
				buildColumns( [
					{ content: 'B', linefeeds: 0 },
					{ content: 'C', linefeeds: 0 }
				] )
			),
			'B\nC'
		);
	} );

	QUnit.test( 'testExtraLineFeedsAreAdded', function ( assert ) {
		assert.strictEqual(
			merger(
				buildColumns( [
					{ content: 'A', linefeeds: 2 },
					{ content: 'B', linefeeds: 0 }
				] )
			),
			'A\n\n\nB'
		);
	} );

	QUnit.test( 'testEmptyLinesAreSkipped', function ( assert ) {
		assert.strictEqual(
			merger(
				buildColumns( [
					{ content: 'A', linefeeds: 0 },
					{ content: '', linefeeds: 2 },
					{ content: 'B', linefeeds: 0 }
				] )
			),
			'A\nB'
		);
	} );
}() );
