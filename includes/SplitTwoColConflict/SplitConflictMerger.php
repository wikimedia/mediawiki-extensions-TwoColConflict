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
	) : string {
		$textLines = [];

		foreach ( $contentRows as $num => $row ) {
			if ( is_array( $sideSelection ) ) {
				// There was no selection to be made for "copy" rows in the interface
				$side = $sideSelection[$num] ?? 'copy';
			} else {
				$side = isset( $row['copy'] ) ? 'copy' : $sideSelection;
			}

			// A mismatch here means the input is either incomplete (by design) or broken, and
			// already detected as such (see above). Intentionally return the most recent, most
			// conflicting result. Fall back to the users conflicting edit, or to *whatever* is
			// there, no matter how invalid the input is. We *never* want to delete a row.
			$line = $row[$side] ??
				$row['your'] ??
				(string)( is_array( $row ) ? current( $row ) : $row );

			// Don't remove all whitespace, because this is not necessarily the end of the article
			$line = rtrim( $line, "\r\n" );

			// In case a line was emptied, we need to skip the extra linefeeds as well
			if ( $line === '' ) {
				continue;
			}

			if ( isset( $extraLineFeeds[$num] ) ) {
				$lf = $extraLineFeeds[$num];
				// Same fallback logic as above, just so we never loose content
				$lf = $lf[$side] ??
					$lf['your'] ??
					(string)( is_array( $lf ) ? current( $lf ) : $lf );
				$lf = explode( ',', $lf );
				// "Before" and "after" are intentionally flipped, because "before" is very rare
				if ( isset( $lf[1] ) ) {
					$line = self::lineFeeds( $lf[1] ) . $line;
				}
				$line .= self::lineFeeds( $lf[0] );
			}

			$textLines[] = $line;
		}
		return SplitConflictUtils::mergeTextLines( $textLines );
	}

	private static function lineFeeds( string $count ) : string {
		$count = (int)$count;

		// Arbitrary limit just to not end with megabytes in case of an attack
		if ( $count <= 0 || $count > 1000 ) {
			return '';
		}

		return str_repeat( "\n", $count );
	}

}
