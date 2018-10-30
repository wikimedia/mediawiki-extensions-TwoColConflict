<?php

namespace TwoColConflict;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class CollapsedTextBuilder {

	const WHITESPACES =
		'\s\xA0\x{1680}\x{180E}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}';

	/**
	 * Get a collapsed version of multi-line text.
	 * Returns false if text is within length-limit.
	 *
	 * @param string $text HTML
	 * @param int $maxLength
	 *
	 * @return string|false
	 */
	public static function buildCollapsedText( $text, $maxLength = 150 ) {
		$text = self::trimWhiteSpaces( html_entity_decode( $text ) );
		$lines = explode( "\n", $text );

		if ( mb_strlen( $text ) <= $maxLength && count( $lines ) <= 2 ) {
			return false;
		}

		return '<span class="mw-twocolconflict-diffchange-fadeout-end">' .
			htmlspecialchars( self::trimStringToFullWord( $lines[0], $maxLength / 2, true ) ) .
			'</span>' .
			( count( $lines ) > 1 ? "\n" : wfMessage( 'word-separator' ) ) .
			'<span class="mw-twocolconflict-diffchange-fadeout-start">' .
			htmlspecialchars(
				self::trimStringToFullWord( array_pop( $lines ), $maxLength / 2, false )
			) .
			'</span>';
	}

	/**
	 * Trims a string at the start or end to the next full word.
	 *
	 * @param string $string
	 * @param int $maxLength
	 * @param boolean $trimAtEnd
	 *
	 * @return string
	 */
	private static function trimStringToFullWord( $string, $maxLength, $trimAtEnd = true ) {
		if ( mb_strlen( $string ) <= $maxLength ) {
			return $string;
		}

		if ( $trimAtEnd ) {
			$result = preg_replace(
				'/[' . self::WHITESPACES . ']+?[^' . self::WHITESPACES . ']+?$/u',
				'',
				mb_substr( $string, 0, $maxLength )
			);
		} else {
			$result = preg_replace(
				'/^[^' . self::WHITESPACES . ']+?[' . self::WHITESPACES . ']+?/u',
				'',
				mb_substr( $string, -$maxLength ),
				1
			);
		}

		return self::trimWhiteSpaces( $result, $trimAtEnd );
	}

	/**
	 * Trims whitespaces and most non-printable characters from a string.
	 *
	 * @param string $string
	 * @param null|boolean $trimAtEnd
	 *
	 * @return string
	 */
	private static function trimWhiteSpaces( $string, $trimAtEnd = null ) {
		if ( $trimAtEnd !== false ) {
			$string = preg_replace( '/[' . self::WHITESPACES . ']+$/u', '', $string );
		}

		if ( $trimAtEnd !== true ) {
			$string = preg_replace( '/^[' . self::WHITESPACES . ']+/u', '', $string );
		}

		return $string;
	}

}
