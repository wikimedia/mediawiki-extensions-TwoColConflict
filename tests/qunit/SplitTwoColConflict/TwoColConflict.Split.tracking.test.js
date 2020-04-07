var UtilModule = require( 'ext.TwoColConflict.Util' ),
	RowFormatter = UtilModule.Tracking.private.RowFormatter;

function buildColumn( params, columnClass, type ) {
	var typeMap = {
			other: 'mw-twocolconflict-split-delete',
			your: 'mw-twocolconflict-split-add',
			copy: 'mw-twocolconflict-split-copy'
		},
		mappedType = typeMap[ type || params.type ],
		classes = [ columnClass, mappedType ];
	return $( '<div>' )
		.addClass( classes )
		.append(
			$( '<textarea>' )
				.addClass( 'mw-twocolconflict-split-editor' )
				.append( params.content )
		)
		.append(
			$( '<span>' )
				.addClass( 'mw-twocolconflict-split-reset-editor-text' )
				.text( params.origContent || params.content )
		);
}

function buildSplitSelector( selection ) {
	var $input = $( '<input>' )
		.attr( 'type', 'radio' );
	if ( selection ) {
		$input
			.prop( 'checked', 'checked' )
			.val( selection );
	}
	return $input;
}

function buildSplitColumn( params, type ) {
	return buildColumn( params, 'mw-twocolconflict-split-column', type );
}

function buildSplitRow( params ) {
	var $row = $( '<div>' )
		.addClass( 'mw-twocolconflict-split-row' );
	if ( params.copy ) {
		return $row
			.append( buildSplitColumn( params.copy, 'copy' ) );
	} else {
		return $row
			.append( buildSplitColumn( params.other, 'other' ) )
			.append( buildSplitSelector( params.selection ) )
			.append( buildSplitColumn( params.your, 'your' ) );
	}
}

function buildSplitRows( paramArray ) {
	return paramArray.map( buildSplitRow );
}

function buildSplitView( paramArray ) {
	return $( '<div>' )
		.append( buildSplitRows( paramArray ) );
}

function buildSingleColumn( params ) {
	return buildColumn( params, 'mw-twocolconflict-single-column' );
}

function buildSingleRow( params ) {
	return $( '<div>' )
		.addClass( 'mw-twocolconflict-single-row' )
		.append( buildSingleColumn( params ) );
}

function buildSingleRows( paramArray ) {
	return paramArray.map( buildSingleRow );
}

function buildSingleView( paramArray ) {
	return $( '<div>' )
		.append( buildSingleRows( paramArray ) );
}

QUnit.module( 'ext.TwoColConflict.Split.tracking' );

QUnit.test( 'test empty', function ( assert ) {
	assert.strictEqual(
		RowFormatter.formatView( buildSingleView( [] ) ),
		'v1:'
	);
} );

QUnit.test( 'test multiple single-column rows', function ( assert ) {
	assert.strictEqual(
		RowFormatter.formatView( buildSingleView( [
			{ type: 'copy', content: 'A' },
			{ type: 'other', content: 'B' },
			{ type: 'your', content: 'C' }
		] ) ),
		'v1:c|o|y'
	);
} );

QUnit.test( 'test edited single-column row', function ( assert ) {
	assert.strictEqual(
		RowFormatter.formatView( buildSingleView( [
			{ type: 'copy', content: 'A' },
			{ type: 'other', content: 'B' },
			{ type: 'your', origContent: 'C1', content: 'C2' }
		] ) ),
		'v1:c|o|y+'
	);
} );

QUnit.test( 'test split-column row selections', function ( assert ) {
	assert.strictEqual(
		RowFormatter.formatView( buildSplitView( [
			{ copy: { content: 'A' } },
			{
				other: { content: 'B' },
				your: { content: 'C' }
			},
			{
				other: { content: 'D' },
				selection: 'your',
				your: { content: 'E' }
			},
			{
				other: { content: 'F' },
				selection: 'other',
				your: { content: 'G' }
			}
		] ) ),
		'v1:c|o?y|o>y|o<y'
	);
} );

QUnit.test( 'test edited split-column row', function ( assert ) {
	assert.strictEqual(
		RowFormatter.formatView( buildSplitView( [
			{ copy: { origContent: 'A1', content: 'A2' } },
			{
				other: { origContent: 'B1', content: 'B' },
				selection: 'other',
				your: { content: 'C' }
			},
			{
				other: { content: 'D' },
				selection: 'your',
				your: { origContent: 'E1', content: 'E2' }
			}
		] ) ),
		'v1:c+|o+<y|o>y+'
	);
} );
