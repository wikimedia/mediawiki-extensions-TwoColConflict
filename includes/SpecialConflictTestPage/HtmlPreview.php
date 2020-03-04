<?php

namespace TwoColConflict\SpecialConflictTestPage;

use ContentHandler;
use ParserOptions;
use Title;
use TwoColConflict\SpecialPageHtmlFragment;

/**
 * Shows a parsed preview of the changed wikitext
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlPreview extends SpecialPageHtmlFragment {

	/**
	 * @param Title $title
	 * @param string $wikiText
	 *
	 * @return string HTML
	 */
	public function getHtml( Title $title, string $wikiText ) : string {
		$content = ContentHandler::makeContent( $wikiText, $title );

		$parserOptions = $this->getParserOptions();

		$pstContent = $content->preSaveTransform(
			$title,
			$this->getUser(),
			$parserOptions
		);

		$scopedCallback = $parserOptions->setupFakeRevision(
			$title,
			$pstContent,
			$this->getUser()
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

	private function getParserOptions() : ParserOptions {
		$parserOptions = new ParserOptions( $this->getUser() );
		$parserOptions->setIsPreview( true );
		return $parserOptions;
	}

}
