<?php

namespace TwoColConflict;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class RandomChangesGenerator {

	/**
	 * Generate random changes in a text given by adding random words from that text
	 *
	 * @param string $baseText Text where random changes should be applied.
	 * @param int $randomWordNum Number of words randomly added.
	 * @param int $minWordLength Min length of word to find and insert.
	 *
	 * @return string Resulting changed base text
	 */
	public static function generateRandomlyChangedText(
		$baseText,
		$randomWordNum = 1,
		$minWordLength = 5
	) {
		for ( $i = 0; $i < $randomWordNum; $i++ ) {
			$randomWord = self::getRandomWord( $baseText, $minWordLength );
			$baseText = self::insertTextAtRandom( $baseText, $randomWord );
		}
		return $baseText;
	}

	/**
	 * Inserts text to a random place in a text. Text will be inserted in a place where a
	 * contiguous flow of characters or numbers is interrupted by other symbols. See
	 * RegExp \pL and \pN definitions.
	 *
	 * @param string $originalText
	 * @param string $textToInsert
	 *
	 * @return string
	 */
	private static function insertTextAtRandom( $originalText, $textToInsert ) {
		preg_match_all( '#[^\pL\pN]+#u', $originalText, $spaces, PREG_OFFSET_CAPTURE );
		$match = $spaces[0][ array_rand( $spaces[0] ) ];
		return substr_replace( $originalText, $match[0] . $textToInsert, $match[1], 0 );
	}

	/**
	 * Returns a random group of contiguous characters or numbers greater than a specific length
	 * from a text. See RegExp \pL and \pN definitions.
	 *
	 * @param string $text
	 * @param int $minLength Min length of word. Will fallback to the best fit after 30 attempts.
	 *
	 * @return string
	 */
	private static function getRandomWord( $text, $minLength ) {
		$randomWord = '';
		$attempts = 0;
		$words = preg_split( '#[^\pL\pN]+#u', $text, -1, PREG_SPLIT_NO_EMPTY );

		while ( mb_strlen( $randomWord ) < $minLength && $attempts < 30 ) {
			$randomWord = $words[ array_rand( $words ) ];
			$attempts++;
		}

		return $randomWord;
	}

}
