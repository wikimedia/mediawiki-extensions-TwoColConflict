<?php

use MediaWiki\MediaWikiServices;

class TwoColConflictPage extends EditPage {

	protected function addExplainConflictHeader( OutputPage $out ) {
		$labelAsPublish = $this->mArticle->getContext()->getConfig()->get(
			'EditSubmitButtonLabelPublish'
		);

		$buttonLabel = $this->context->msg(
			$labelAsPublish ? 'publishchanges' : 'savechanges'
		)->text();

		$out->wrapWikiMsg(
			"<div class='mw-twocolconflict-explainconflict'>\n$1\n</div>",
			$this->context->msg( 'twoColConflict-explainconflict', $buttonLabel )
		);
	}

	public function showEditForm( $formCallback = null ) {
		if ( $this->isConflict ) {
			$this->addModules();
			$this->editFormTextBeforeContent = $this->addEditFormBeforeContent();
			$this->editFormTextAfterContent = $this->addEditFormAfterContent();
		}

		parent::showEditForm( $formCallback );
	}

	// For the first version show at least the default diff view.
	protected function showConflict() {
		global $wgOut;
		// TODO What should happen if someone calls the hook in here?

		$wgOut->wrapWikiMsg( '<h2>$1</h2>', "yourdiff" );

		$content1 = $this->toEditContent( $this->textbox1 );
		$content2 = $this->toEditContent( $this->textbox2 );

		$handler = ContentHandler::getForModelID( $this->contentModel );
		$de = $handler->createDifferenceEngine( $this->mArticle->getContext() );
		$de->setContent( $content2, $content1 );
		$de->showDiff(
			$this->context->msg( 'yourtext' )->parse(),
			$this->context->msg( 'storedversion' )->text()
		);
	}

	private function addEditFormBeforeContent() {
		return $this->buildConflictPageChangesCol() . $this->buildConflictPageEditorCol();
	}

	private function addEditFormAfterContent() {
		return '</div>';
	}

	private function buildConflictPageChangesCol() {
		global $wgUser;

		$lastUser = $this->mArticle->getPage()->getUserText();
		$lastChangeTime = $this->getContext()->getLanguage()->userTimeAndDate(
			$this->mArticle->getPage()->getTimestamp(),
			$wgUser
		);
		$yourChangeTime = $this->getContext()->getLanguage()->userTimeAndDate(
			time(),
			$wgUser
		);

		$out = '<div class="mw-twocolconflict-changes-col">';
		$out.= '<h3>' . $this->getContext()->msg( 'twoColConflict-changes-col-title' ) . '</h3>';
		$out.= '<div class="mw-twocolconflict-col-desc">' . $this->getContext()->msg(
			'twoColConflict-changes-col-desc', $lastUser, $lastChangeTime, $yourChangeTime
			) . '</div>';
		$out.= $this->buildChangesTextbox();
		$out.= '</div>';

		return $out;
	}

	private function buildChangesTextbox() {
		global $wgUser;

		$name = 'mw-twocolconflict-changes-editor';
		$wikitext = $this->safeUnicodeOutput( $this->textbox1 );
		$wikitext = $this->addNewLineAtEnd( $wikitext );

		$customAttribs = [];
		if ( $this->wikiEditorIsEnabled() ) {
			$customAttribs[ 'class' ] = 'mw-twocolconflict-wikieditor';
		}

		$attribs = $this->buildTextboxAttribs( $name, $customAttribs, $wgUser );

		return Html::rawElement( 'div', $attribs, $wikitext );
	}

	private function buildConflictPageEditorCol() {
		global $wgUser;

		$lastUser = $this->mArticle->getPage()->getUserText();
		$lastChangeTime = $this->mArticle->getPage()->getTimestamp();
		$lastChangeTime = $this->getContext()->getLanguage()->userTimeAndDate( $lastChangeTime, $wgUser );

		$out = '<div class="mw-twocolconflict-editor-col">';
		$out.= '<h3>' . $this->getContext()->msg( 'twoColConflict-editor-col-title' ) . '</h3>';
		$out.= '<div class="mw-twocolconflict-col-desc">' . $this->getContext()->msg(
			'twoColConflict-editor-col-desc', $lastUser, $lastChangeTime
			) . '</div>';

		return $out;
	}

	private function addModules() {
		$this->loadAndAddModule( 'ext.TwoColConflict.editor' );
	}

	private function loadAndAddModule( $name ) {
		global $wgOut;

		if ( $wgOut->getResourceLoader()->isModuleRegistered( $name ) ) {
			$wgOut->addModules( $name );
		}
	}

	private function wikiEditorIsEnabled() {
		return class_exists( WikiEditorHooks::class ) && WikiEditorHooks::isEnabled( 'toolbar' );
	}
}
