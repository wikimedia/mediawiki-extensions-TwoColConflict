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

	/**
	 * @return array
	 */
	public function getDiff(): array {
		return $this->diff;
	}

	/**
	 * @return int
	 */
	public function getOtherIndex(): int {
		return $this->otherIndex;
	}

	/**
	 * @return int
	 */
	public function getYourIndex(): int {
		return $this->yourIndex;
	}
}