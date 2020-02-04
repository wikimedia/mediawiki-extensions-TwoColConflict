<?php

namespace TwoColConflict\SpecialConflictTestPage;

use EditPage;
use Html;
use TwoColConflict\SpecialPageHtmlFragment;

/**
 * TODO: It might be worth extracting this class to a library or MediaWiki core, because a duplicate
 * already exists in \FileImporter\Html\WikitextEditor.
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class HtmlWikiTextEditor extends SpecialPageHtmlFragment {

	/**
	 * @param string $wikiText
	 *
	 * @return string HTML
	 */
	public function getHtml( string $wikiText ) : string {
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
		$editPage->setContextTitle( $this->getPageTitle() );

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
	private function buildEditor( string $wikiText ) : string {
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
	private function addNewLineAtEnd( string $wikiText ) : string {
		return $wikiText === '' ? '' : $wikiText . "\n";
	}

}
