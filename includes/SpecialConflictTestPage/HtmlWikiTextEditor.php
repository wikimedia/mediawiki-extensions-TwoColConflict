<?php

namespace TwoColConflict\SpecialConflictTestPage;

use EditPage;
use Html;
use TwoColConflict\SpecialPageHtmlFragment;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlWikiTextEditor extends SpecialPageHtmlFragment {

	/**
	 * @param string $wikiText
	 *
	 * @return string HTML
	 */
	public function getHtml( $wikiText ) {
		$this->loadModules();
		$this->runEditFormInitialHook();

		return EditPage::getEditToolbar() .
			$this->buildEditor( $wikiText );
	}

	/**
	 * Load modules mainly related to the toolbar functions
	 */
	private function loadModules() {
		$this->getOutput()->addModules( 'mediawiki.action.edit' );
		$this->getOutput()->addModuleStyles( 'mediawiki.action.edit.styles' );
	}

	/**
	 * Run EditPage::showEditForm:initial hook mainly for the WikiEditor toolbar
	 * See WikiEditorHooks::editPageShowEditFormInitial
	 * Triggering the hook means we don't have special handling for any extensions.
	 */
	private function runEditFormInitialHook() {
		$editPage = new EditPage(
			\Article::newFromTitle(
				$this->getPageTitle(),
				$this->getContext()
			)
		);

		\Hooks::run( 'EditPage::showEditForm:initial',
			[ &$editPage, $this->getOutput() ]
		);
	}

	/**
	 * Build editor HTML see EditPage->showTextbox()
	 *
	 * @param string $wikiText
	 *
	 * @return string
	 */
	private function buildEditor( $wikiText ) {
		$class = 'mw-editfont-' . $this->getUser()->getOption( 'editfont' );
		$pageLang = $this->getLanguage();

		$wikiText = $this->addNewLineAtEnd( $wikiText );

		$attributes = [
			'id' => 'wpTextbox1',
			'class' => $class . ' mw-twocolconflict-test-text',
			'cols' => 80,
			'rows' => 25,
			'accesskey' => ',',
			'tabindex' => 3,
			'lang' => $pageLang->getHtmlCode(),
			'dir' => $pageLang->getDir(),
			'autofocus' => 'autofocus',
		];

		return Html::textarea( 'mw-twocolconflict-test-text', $wikiText, $attributes );
	}

	/**
	 * Build editor HTML see EditPage->addNewLineAtEnd()
	 *
	 * @param string $wikiText
	 *
	 * @return string
	 */
	private function addNewLineAtEnd( $wikiText ) {
		if ( strval( $wikiText ) !== '' ) {
			// Ensure there's a newline at the end, otherwise adding lines
			// is awkward.
			// But don't add a newline if the text is empty, or Firefox in XHTML
			// mode will show an extra newline. A bit annoying.
			$wikiText .= "\n";
			return $wikiText;
		}
		return $wikiText;
	}

}
