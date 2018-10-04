<?php

namespace TwoColConflict\SplitTwoColConflict;

class SplitConflictMerger {

	/**
	 * @param array[] $contentRows
	 * @param array[] $extraLineFeeds
	 * @param string[] $sideSelection
	 * @return string
	 */
	public static function mergeSplitConflictResults(
		array $contentRows,
		array $extraLineFeeds,
		array $sideSelection
	) {
		$textLines = [];
		foreach ( $contentRows as $num => $row ) {
			$side = isset( $sideSelection[$num] ) ? $sideSelection[$num] : 'copy';
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
