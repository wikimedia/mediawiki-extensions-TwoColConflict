<?php

namespace TwoColConflict\SpecialConflictTestPage;

use ContentHandler;
use MWContentSerializationException;
use ParserOptions;
use SpecialPage;
use Title;

/**
 * Shows a parsed preview of the changed wikitext
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlPreview {

	/**
	 * @var SpecialPage
	 */
	private $specialPage;

	/**
	 * @param SpecialPage $specialPage
	 */
	public function __construct( SpecialPage $specialPage ) {
		$this->specialPage = $specialPage;
	}

	/**
	 * @param Title $title
	 * @param string $wikiText
	 *
	 * @return string HTML
	 */
	public function getHtml( $title, $wikiText ) {
		try {
			$content = ContentHandler::makeContent( $wikiText, $title );
		} catch ( MWContentSerializationException $ex ) {
			die( 'failed to parse content of latest revision' );
		}

		$parserOptions = $this->getParserOptions();

		$pstContent = $content->preSaveTransform(
			$title,
			$this->specialPage->getUser(),
			$parserOptions
		);

		$scopedCallback = $parserOptions->setupFakeRevision(
			$title,
			$pstContent,
			$this->specialPage->getUser()
		);

		$parseResult = $pstContent->getParserOutput(
			$title,
			null,
			$this->getParserOptions()
		);

		\Wikimedia\ScopedCallback::consume( $scopedCallback );

		return $parseResult->getText( [
			'enableSectionEditLinks' => false,
		] );
	}

	private function getParserOptions() {
		$parserOptions = new ParserOptions();
		$parserOptions->setIsPreview( true );
		return $parserOptions;
	}

}
