<?php

namespace TwoColConflict\SplitTwoColConflict;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SplitConflictUtils {

	/**
	 * @param string $text
	 *
	 * @return string[]
	 */
	public static function splitText( $text ) {
		return preg_split( '/\n(?!\n)/', str_replace( [ "\r\n", "\r" ], "\n", $text ) );
	}

	/**
	 * @param string[] $textLines
	 *
	 * @return string
	 */
	public static function mergeTextLines( $textLines ) {
		return str_replace( [ "\r\n", "\r" ], "\n", implode( "\n", $textLines ) );
	}

}