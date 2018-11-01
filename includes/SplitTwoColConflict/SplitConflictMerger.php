<?php

namespace TwoColConflict\SplitTwoColConflict;

/**
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class SplitConflictMerger {

	/**
	 * @param array[] $contentRows
	 * @param array[] $extraLineFeeds
	 * @param string[]|string $sideSelection Either an array of side identifiers per row ("copy",
	 *  "other", or "your"). Or one side identifier for all rows (either "other" or "your").
	 *
	 * @return string Wikitext
	 */
	public static function mergeSplitConflictResults(
		array $contentRows,
		array $extraLineFeeds,
		$sideSelection
	) {
		$textLines = [];

		foreach ( $contentRows as $num => $row ) {
			if ( is_array( $sideSelection ) ) {
				$side = isset( $sideSelection[$num] ) ? $sideSelection[$num] : 'copy';
			} else {
				$side = isset( $row['copy'] ) ? 'copy' : $sideSelection;
			}

			// As all this is user input, we can't assume the elements are always there
			if ( !isset( $row[$side] ) ) {
				continue;
			}

			$line = rtrim( $row[$side], "\r\n" );
			if ( $line === '' ) {
				continue;
			}

			if ( isset( $extraLineFeeds[$num][$side] ) ) {
				$line .= str_repeat( "\n", $extraLineFeeds[$num][$side] );
			}

			$textLines[] = $line;
		}

		return implode( "\n", $textLines );
	}

}
