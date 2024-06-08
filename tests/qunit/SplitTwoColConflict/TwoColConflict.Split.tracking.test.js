const RowFormatter = require( 'ext.TwoColConflict.SplitJs' ).private.RowFormatter;

function buildColumn( params, columnClass, type ) {
	const typeMap = {
			other: 'mw-twocolconflict-split-delete',
			your: 'mw-twocolconflict-split-add',
			copy: 'mw-twocolconflict-split-copy'
		},
		mappedType = typeMap[ type || params.type ],
		classes = [ columnClass, mappedType ];

	// The following classes are used here:
	// * mw-twocolconflict-split-delete
	// * mw-twocolconflict-split-add
	// * mw-twocolconflict-split-copy
	// * mw-twocolconflict-split-column
	// * mw-twocolconflict-single-column
	return $( '<div>' )
		.addClass( classes )
		.append(
			$( '<textarea>' )
				.addClass( 'mw-twocolconflict-split-editor' )
				.append( params.content )
				.val( params.editedContent || params.content )
		);
}

function buildSplitSelector( selection ) {
	const $input = $( '<input>' )
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
	const $row = $( '<div>' )
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

QUnit.test( 'test empty', ( assert ) => {
	assert.strictEqual(
		RowFormatter.formatView( buildSingleView( [] ) ),
		'v1:'
	);
} );

QUnit.test( 'test multiple single-column rows', ( assert ) => {
	assert.strictEqual(
		RowFormatter.formatView( buildSingleView( [
			{ type: 'copy', content: 'A' },
			{ type: 'other', content: 'B' },
			{ type: 'your', content: 'C' }
		] ) ),
		'v1:c|o|y'
	);
} );

QUnit.test( 'test edited single-column row', ( assert ) => {
	assert.strictEqual(
		RowFormatter.formatView( buildSingleView( [
			{ type: 'copy', content: 'A' },
			{ type: 'other', content: 'B' },
			{ type: 'your', content: 'C1', editedContent: 'C2' }
		] ) ),
		'v1:c|o|y+'
	);
} );

QUnit.test( 'test split-column row selections', ( assert ) => {
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

QUnit.test( 'test edited split-column row', ( assert ) => {
	assert.strictEqual(
		RowFormatter.formatView( buildSplitView( [
			{ copy: { content: 'A1', editedContent: 'A2' } },
			{
				other: { content: 'B1', editedContent: 'B' },
				selection: 'other',
				your: { content: 'C' }
			},
			{
				other: { content: 'D' },
				selection: 'your',
				your: { content: 'E1', editedContent: 'E2' }
			}
		] ) ),
		'v1:c+|o+<y|o>y+'
	);
} );
