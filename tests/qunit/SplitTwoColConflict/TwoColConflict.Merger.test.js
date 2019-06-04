( function () {
	function ConflictBuilder() {
		this.$rows = $( '<div>' )
			.addClass( 'mw-twocolconflict-split-view' );
	}

	ConflictBuilder.prototype = {
		/**
		 * @param {string} content
		 * @param {number} lines
		 * @param {boolean|string} selectionClass
		 * @return {jQuery}
		 */
		buildColumn: function ( content, lines, selectionClass ) {
			if ( typeof selectionClass !== 'string' ) {
				selectionClass = selectionClass ?
					'mw-twocolconflict-split-selected' :
					'mw-twocolconflict-split-unselected';
			}

			return $( '<div>' )
				.addClass( 'mw-twocolconflict-split-column ' + selectionClass )
				.append(
					$( '<textarea>' )
						.addClass( 'mw-twocolconflict-split-editor' )
						.append( content )
				)
				.append(
					$( '<input>' )
						.attr( 'name', 'mw-twocolconflict-split-linefeeds' )
						.val( lines )
				);
		},

		/**
		 * @param {Object[]} columns
		 * @return {jQuery}
		 */
		buildRow: function ( columns ) {
			var $row = $( '<div>' )
				.addClass( 'mw-twocolconflict-split-row' );

			columns.forEach( function ( $column ) {
				$row.append( $column );
			} );
			return $row;
		},

		/**
		 * @param {string} content
		 * @param {number} lines
		 * @return {ConflictBuilder}
		 */
		addRowCopy: function ( content, lines ) {
			this.$rows.append( this.buildRow( [
				this.buildColumn( content, lines, 'mw-twocolconflict-split-copy' )
			] ) );
			return this;
		},

		/**
		 * @param {string} contentOther
		 * @param {string} contentYours
		 * @param {boolean} selectedOther
		 * @param {number} linesOther
		 * @param {number} linesYours
		 * @return {ConflictBuilder}
		 */
		addRowChange: function (
			contentOther,
			contentYours,
			selectedOther,
			linesOther,
			linesYours
		) {
			this.$rows.append( this.buildRow( [
				this.buildColumn( contentOther, linesOther, selectedOther ),
				this.buildColumn( contentYours, linesYours, !selectedOther )
			] ) );
			return this;
		},

		/**
		 * @return {jQuery}
		 */
		getHtml: function () {
			return this.$rows;
		}
	};

	QUnit.module( 'ext.TwoColConflict.Split.Merger' );
	var merger = mw.libs.twoColConflict.split.merger;

	QUnit.test( 'testSingleCopyRow', function ( assert ) {
		assert.strictEqual(
			merger(
				new ConflictBuilder()
					.addRowCopy( 'A', 0 )
					.getHtml()
			),
			'A'
		);
	} );

	QUnit.test( 'testStaticSideSelection', function ( assert ) {
		assert.strictEqual(
			merger(
				new ConflictBuilder()
					.addRowChange( 'A', 'B', false, 0, 0 )
					.getHtml()
			),
			'B'
		);
	} );

	QUnit.test( 'testMixedSideSelection', function ( assert ) {
		assert.strictEqual(
			merger(
				new ConflictBuilder()
					.addRowChange( 'A', 'B', false, 0, 0 )
					.addRowChange( 'C', 'D', true, 0, 0 )
					.getHtml()
			),
			'B\nC'
		);
	} );

	QUnit.test( 'testExtraLineFeedsAreAdded', function ( assert ) {
		assert.strictEqual(
			merger(
				new ConflictBuilder()
					.addRowCopy( 'A', 2 )
					.addRowCopy( 'B', 0 )
					.getHtml()
			),
			'A\n\n\nB'
		);
	} );

	QUnit.test( 'testEmptyLinesAreSkipped', function ( assert ) {
		assert.strictEqual(
			merger(
				new ConflictBuilder()
					.addRowCopy( 'A', 0 )
					.addRowCopy( '', 2 )
					.addRowCopy( 'B', 0 )
					.getHtml()
			),
			'A\nB'
		);
	} );
}() );
