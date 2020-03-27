<?php

namespace TwoColConflict\SplitTwoColConflict;

use WebRequest;

class ConflictFormValidator {

	/**
	 * Check whether inputs are valid.  Note that a POST without conflict fields is considered
	 * valid.
	 *
	 * @param WebRequest $request
	 * @return bool True when valid
	 */
	public function validateRequest( WebRequest $request ) : bool {
		$contentRows = $request->getArray( 'mw-twocolconflict-split-content' );
		if ( $contentRows === null ) {
			// Not a conflict form.
			return true;
		}
		if ( $contentRows === [] ) {
			// Empty conflict form is bad.
			return false;
		}

		$sideSelection = $request->getArray( 'mw-twocolconflict-side-selector', [] );
		if ( $sideSelection ) {
			return $this->validateSideSelection( $contentRows, $sideSelection );
		}

		return false;
	}

	/**
	 * @param array[] $contentRows
	 * @param string[] $sideSelection
	 *
	 * @return bool
	 */
	private function validateSideSelection( array $contentRows, array $sideSelection ) : bool {
		foreach ( $contentRows as $num => $row ) {
			$side = $sideSelection[$num] ?? 'copy';
			if ( !isset( $row[$side] ) || !is_string( $row[$side] ) ) {
				return false;
			}
		}

		return true;
	}

}
