<?php

/**
 * @license GNU GPL v2+
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictPage extends EditPage {

	/**
	 * TwoColConflictPage constructor.
	 *
	 * @param Article $article
	 */
	public function __construct( Article $article ) {
		parent::__construct( $article );
	}

	/**
	 * Replace default header for explaining the conflict screen.
	 *
	 * @param OutputPage $out
	 */
	protected function addExplainConflictHeader( OutputPage $out ) {
		$labelAsPublish = $this->mArticle->getContext()->getConfig()->get(
			'EditSubmitButtonLabelPublish'
		);

		$buttonLabel = $this->getContext()->msg(
			$labelAsPublish ? 'publishchanges' : 'savechanges'
		)->text();

		$out->wrapWikiMsg(
			"<div class='mw-twocolconflict-explainconflict'>\n$1\n</div>",
			$this->getContext()->msg( 'twoColConflict-explainconflict', $buttonLabel )
		);
	}

	/**
	 * Set the HTML to encapsulate the default edit form.
	 *
	 * @param callable|null $formCallback
	 */
	public function showEditForm( $formCallback = null ) {
		if ( $this->isConflict ) {
			$this->addCSS();
			$this->editFormTextTop = '<div class="mw-twocolconflict-form">';
			$this->editFormTextBottom = '</div>';
			$this->editFormTextBeforeContent = $this->addEditFormBeforeContent();
			$this->editFormTextAfterContent = $this->addEditFormAfterContent();
		}

		parent::showEditForm( $formCallback );
	}

	protected function showConflict() {
		// don't show the original conflict view at the bottom
		return false;
	}

	/**
	 * Build HTML that will be added before the default edit form.
	 *
	 * @return string
	 */
	private function addEditFormBeforeContent() {
		return $this->buildConflictPageChangesCol() . $this->buildConflictPageEditorCol();
	}

	/**
	 * Build HTML content that will be added after the default edit form.
	 *
	 * @return string
	 */
	private function addEditFormAfterContent() {
		// this div is opened when encapsulating the editor in buildConflictPageEditorCol.
		return '</div>';
	}

	/**
	 * Build HTML that will add the textbox with the unified diff.
	 *
	 * @return string
	 */
	private function buildConflictPageChangesCol() {
		global $wgUser;

		$lastUser =
			'<span class="mw-twocolconflict-lastuser">' .
			$this->mArticle->getPage()->getUserText() .
			'</span>';
		$lastChangeTime = $this->getContext()->getLanguage()->userTimeAndDate(
			$this->getArticle()->getPage()->getTimestamp(),
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

	/**
	 * Build HTML for the textbox with the unified diff.
	 *
	 * @return string
	 */
	private function buildChangesTextbox() {
		global $wgUser;

		$name = 'mw-twocolconflict-changes-editor';
		$wikitext = $this->safeUnicodeOutput( $this->getUnifiedDiffText() );
		$wikitext = $this->addNewLineAtEnd( $wikitext );

		$customAttribs = [
			'tabindex' => 0
		];
		if ( $this->wikiEditorIsEnabled() ) {
			$customAttribs[ 'class' ] = 'mw-twocolconflict-wikieditor';
		}

		$attribs = $this->buildTextboxAttribs( $name, $customAttribs, $wgUser );

		return Html::rawElement( 'div', $attribs, $wikitext );
	}

	/**
	 * Build HTML to encapsulate editor with the conflicting text.
	 *
	 * @return string
	 */
	private function buildConflictPageEditorCol() {
		global $wgUser;

		$lastUser = $this->getArticle()->getPage()->getUserText();
		$lastChangeTime = $this->getArticle()->getPage()->getTimestamp();
		$lastChangeTime = $this->getContext()->getLanguage()->userTimeAndDate( $lastChangeTime, $wgUser );

		$out = '<div class="mw-twocolconflict-editor-col">';
		$out.= '<h3>' . $this->getContext()->msg( 'twoColConflict-editor-col-title' ) . '</h3>';
		$out.= '<div class="mw-twocolconflict-col-desc">' . $this->getContext()->msg(
			'twoColConflict-editor-col-desc', $lastUser, $lastChangeTime
			) . '</div>';

		return $out;
	}

	/**
	 * Get array with line based diff changes.
	 *
	 * @param string[] $fromTextLines
	 * @param string[] $toTextLines
	 * @return array[]
	 */
	private function getLineBasedUnifiedDiff( $fromTextLines, $toTextLines ) {
		$formatter = new LineBasedUnifiedDiffFormatter();
		$formatter->insClass = ' class="mw-twocolconflict-diffchange"';
		$formatter->delClass = ' class="mw-twocolconflict-diffchange"';
		return $formatter->format(
			new Diff( $fromTextLines, $toTextLines )
		);
	}

	/**
	 * Build HTML for the content of the unified diff box.
	 *
	 * @return string
	 */
	private function getUnifiedDiffText() {
		$lastUser = $this->getArticle()->getPage()->getUserText();
		$currentText = $this->toEditText( $this->getCurrentContent() );
		$yourText = $this->textbox1;

		$currentLines = explode( "\n", $currentText );
		$yourLines = explode( "\n", $yourText );

		$combinedChanges = $this->getLineBasedUnifiedDiff( $currentLines, $yourLines );

		$output = [];
		foreach ( $currentLines as $key => $currentLine ) {
			++$key;
			if ( isset( $combinedChanges[ $key ] ) ) {
				foreach ( $combinedChanges[ $key ] as $changeSet ) {
					switch ( $changeSet[ 'action' ] ) {
						case 'add':
							$output[] = '<div class="mw-twocolconflict-diffchange-own">' .
								'<div class="mw-twocolconflict-diffchange-title" ' .
								'unselectable="on">' . // used by IE9
								$this->getContext()->msg( 'twoColConflict-diffchange-own-title' ) .
								'</div>';
							$output[] = $changeSet['new'] . '</div>';
							break;
						case 'delete':
							$output[] = '<div class="mw-twocolconflict-diffchange-foreign">' .
								'<div class="mw-twocolconflict-diffchange-title" ' .
								'unselectable="on">' . // used by IE9
								$this->getContext()->msg(
									'twoColConflict-diffchange-foreign-title',
									$lastUser
								) .
								'</div>';
							$output[] = $changeSet['old'] . '</div>';
							break;
						case 'copy':
							$output[] = '<div class="mw-twocolconflict-diffchange-same">' .
								$changeSet['copy'] . '</div>';
							break;
					}
				}
			}
		}

		return implode( "\n", $output );
	}

	private function wikiEditorIsEnabled() {
		return class_exists( WikiEditorHooks::class ) && WikiEditorHooks::isEnabled( 'toolbar' );
	}

	private function addCSS() {
		global $wgOut;

		if ( $wgOut->getResourceLoader()->isModuleRegistered( 'ext.TwoColConflict.editor' ) ) {
			$wgOut->addModuleStyles( 'ext.TwoColConflict.editor' );
		}
	}
}
