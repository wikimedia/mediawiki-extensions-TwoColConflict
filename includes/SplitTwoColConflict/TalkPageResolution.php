<?php

namespace TwoColConflict\SplitTwoColConflict;

/**
 * Container for talk page use case resolution.  This is populated by the suggester, and can be
 * manipulated by editing your text or reordering your and the other text block.
 */
class TalkPageResolution {
	/** @var array */
	private $diff;
	/** @var int */
	private $otherIndex;
	/** @var int */
	private $yourIndex;

	/**
	 * @param array $diff
	 * @param int $otherIndex
	 * @param int $yourIndex
	 */
	public function __construct( array $diff, int $otherIndex, int $yourIndex ) {
		$this->diff = $diff;
		$this->otherIndex = $otherIndex;
		$this->yourIndex = $yourIndex;
	}
}
