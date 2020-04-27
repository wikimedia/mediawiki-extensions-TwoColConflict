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
					.val( params.linefeeds || 0 )
			);
	}

	function buildColumns( paramArray ) {
		return paramArray.map( buildColumn ).reduce( $.merge );
	}

	QUnit.module( 'ext.TwoColConflict.Split.Merger' );
	var UtilModule = require( 'ext.TwoColConflict.Util' ),
		merger = UtilModule.Merger;

	QUnit.test( 'testSingleCopyRow', function ( assert ) {
		assert.strictEqual(
			merger(
				buildColumns( [
					{ content: 'A' }
				] )
			),
			'A'
		);
	} );

	QUnit.test( 'testMultipleColumns', function ( assert ) {
		assert.strictEqual(
			merger(
				buildColumns( [
					{ content: 'B' },
					{ content: 'C' }
				] )
			),
			'B\nC'
		);
	} );

	QUnit.test( 'testExtraLineFeedsAreAdded', function ( assert ) {
		assert.strictEqual(
			merger(
				buildColumns( [
					{ content: 'A', linefeeds: '2,1' },
					{ content: 'B' }
				] )
			),
			'\nA\n\n\nB'
		);
	} );

	QUnit.test( 'testEmptyLinesAreSkipped', function ( assert ) {
		assert.strictEqual(
			merger(
				buildColumns( [
					{ content: 'A' },
					{ content: '', linefeeds: 2 },
					{ content: 'B' }
				] )
			),
			'A\nB'
		);
	} );

	QUnit.test( 'testRowsNotEmptiedByTheUserAreNotIgnored', function ( assert ) {
		assert.strictEqual(
			merger(
				buildColumns( [
					{ content: '', linefeeds: '1,was-empty' },
					{ content: 'A' }
				] )
			),
			'\n\nA'
		);
	} );
}() );
