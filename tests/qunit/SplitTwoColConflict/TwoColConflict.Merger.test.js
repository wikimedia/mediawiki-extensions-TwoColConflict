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
	const merger = require( 'ext.TwoColConflict.SplitJs' ).private.Merger;

	QUnit.test( 'testSingleCopyRow', ( assert ) => {
		assert.strictEqual(
			merger(
				buildColumns( [
					{ content: 'A' }
				] )
			),
			'A'
		);
	} );

	QUnit.test( 'testMultipleColumns', ( assert ) => {
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

	QUnit.test( 'testExtraLineFeedsAreAdded', ( assert ) => {
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

	QUnit.test( 'testEmptyLinesAreSkipped', ( assert ) => {
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

	QUnit.test( 'testRowsNotEmptiedByTheUserAreNotIgnored', ( assert ) => {
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
