<?php
use MediaWiki\MediaWikiServices;

/**
 * @license GNU GPL v2+
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictPage extends EditPage {

	const WHITESPACES =
		'\s\xA0\x{1680}\x{180E}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}';

	/**
	 * Increment stats to count conflicts handled
	 */
	protected function incrementConflictStats() {
		parent::incrementConflictStats();
		$stats = MediaWikiServices::getInstance()->getStatsdDataFactory();
		$stats->increment( 'TwoColConflict.conflict' );
	}

	/**
	 * Replace default header for explaining the conflict screen.
	 *
	 * @param OutputPage $out OutputPage used for page output.
	 */
	protected function addExplainConflictHeader( OutputPage $out ) {
		// don't show conflict message when coming from VisualEditor
		if ( $this->getContext()->getRequest()->getVal( 'veswitched' ) !== "1" ) {
			$out->wrapWikiMsg(
				"<div class='mw-twocolconflict-explainconflict warningbox'>\n$1\n</div>",
				[ 'twoColConflict-explainconflict', $this->getSubmitButtonLabel() ]
			);
		}
	}

	private function getSubmitButtonLabel() {
		$labelAsPublish = $this->context->getConfig()->get(
			'EditSubmitButtonLabelPublish'
		);

		return $this->context->msg(
			$labelAsPublish ? 'publishchanges' : 'savechanges'
		)->text();
	}

	/**
	 * Set the HTML to encapsulate the default edit form.
	 *
	 * @param callable|null $formCallback That takes an OutputPage parameter; will be called
	 *     during form output near the top, for captchas and the like.
	 */
	public function showEditForm( $formCallback = null ) {
		if ( $this->isConflict ) {
			$this->addCSS();
			$this->addJS();
			$this->deactivateWikEd();
			$this->editFormTextTop = '<div class="mw-twocolconflict-form mw-twocolconflict-before-base-selection">';
			$this->editFormTextBottom = '</div>';
			$this->editFormTextBeforeContent = $this->addEditFormBeforeContent();
			$this->editFormTextAfterContent = $this->addEditFormAfterContent();
		}

		parent::showEditForm( $formCallback );
	}

	/**
	 * Shows the diff part in the original conflict handling. Is not
	 * used and overwritten.
	 *
	 * @return bool
	 */
	protected function showConflict() {
		$this->incrementConflictStats();

		// don't show the original conflict view at the bottom
		return false;
	}

	/**
	 * Build HTML that will be added before the default edit form.
	 *
	 * @return string
	 */
	private function addEditFormBeforeContent() {
		$out = HTML::input( 'mw-twocolconflict-submit', 'true', 'hidden' );
		$out .= $this->buildConflictPageChangesCol();

		$editorClass = '';
		if ( $this->wikiEditorIsEnabled() ) {
			$editorClass = ' mw-twocolconflict-wikieditor';
		}
		$out .= '<div class="mw-twocolconflict-editor-col' . $editorClass . '">';
		$out .= $this->buildConflictPageEditorCol();
		$out .= $this->buildMyVersionTextHiddenField();

		return $out;
	}

	/**
	 * Build HTML content that will be added after the default edit form.
	 *
	 * @return string
	 */
	private function addEditFormAfterContent() {
		// this div is opened when encapsulating the default editor in addEditFormBeforeContent.
		return '</div><div style="clear: both"></div>';
	}

	/**
	 * Build HTML that will add the textbox with the unified diff.
	 *
	 * @return string
	 */
	private function buildConflictPageChangesCol() {
		$out = '<div class="mw-twocolconflict-changes-col">';
		$out .= '<div class="mw-twocolconflict-col-header">';
		$out .= '<h3>' . $this->getContext()->msg( 'twoColConflict-changes-col-title' )->parse() .
			'</h3>';
		$out .= '<div class="mw-twocolconflict-col-desc">';
		$out .= $this->getContext()->msg( 'twoColConflict-changes-col-desc-1' )->text();
		$out .= '<ul>';
		$out .= '';
		$out .= '<li><span class="mw-twocolconflict-lastuser">' .
			$this->getContext()->msg( 'twoColConflict-changes-col-desc-2' )->text() . '</span><br/>' .
			$this->buildEditSummary() . '</li>';
		$out .= '<li><span class="mw-twocolconflict-user">' .
			$this->getContext()->msg( 'twoColConflict-changes-col-desc-4' )->text() . '</span></li>';
		$out .= '</ul>';
		$out .= '</div>';
		$out .= '</div>';

		$out .= $this->buildFilterOptionsMenu();

		$unifiedDiff = $this->getUnifiedDiff();

		$out .= $this->buildChangesTextbox( $this->getMarkedUpDiffText( $unifiedDiff ) );
		$out .= $this->buildHiddenChangesTextbox( $this->getMarkedUpForeignText( $unifiedDiff ) );
		$out .= '</div>';

		return $out;
	}

	/**
	 * Build HTML for the filter options for the unified diff view.
	 *
	 * @return string
	 */
	private function buildFilterOptionsMenu() {
		$this->context->getOutput()->enableOOUI();

		$showHideOptions = new OOUI\RadioSelectInputWidget( [
			'options' => [
				[
					'data' => 'show',
					'label' => $this->getContext()->msg( 'twoColConflict-label-show' )->text()
				],
				[
					'data' => 'hide',
					'label' => $this->getContext()->msg( 'twoColConflict-label-hide' )->text()
				],
			],
			'name' => 'mw-twocolconflict-same',
			'title' => 'mw-twocolconflict-same',
			'classes' => [ 'mw-twocolconflict-filter-options-btn' ]
		] );

		$out = '<div class="mw-twocolconflict-filter-options-container">';

		$out .= '<div class="mw-twocolconflict-filter-options-row">';
		$out .= '<div class="mw-twocolconflict-filter-titles">' .
			$this->getContext()->msg( 'twoColConflict-label-unchanged' )->text() .
			'</div>';
		$out .= $showHideOptions;
		$out .= $this->buildHelpButton();
		$out .= '</div>';

		$out .= '</div>';

		return $out;
	}

	/**
	 * Build HTML for the help button for the unified diff view.
	 *
	 * @return string
	 */
	private function buildHelpButton() {
		// Load icon pack with the 'help' icon
		$this->context->getOutput()->addModuleStyles( 'oojs-ui.styles.icons-content' );

		$helpButton = new OOUI\ButtonInputWidget( [
			'icon' => 'help',
			'framed' => false,
			'name' => 'mw-twocolconflict-show-help',
			'title' => $this->getContext()->msg( 'twoColConflict-show-help-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-show-help' ]
		] );

		$out = '<div class="mw-twocolconflict-show-help-container">';
		$out .= $helpButton;
		$out .= '</div>';

		return $out;
	}

	/**
	 * Build HTML for the textbox with the unified diff.
	 *
	 * @param string $wikiText
	 * @return string
	 */
	private function buildChangesTextbox( $wikiText ) {
		$name = 'mw-twocolconflict-changes-editor';

		$customAttribs = [
			'tabindex' => 0,
			'class' => $name
		];
		if ( $this->wikiEditorIsEnabled() ) {
			$customAttribs['class'] .= ' mw-twocolconflict-wikieditor';
		}

		$attribs = $this->buildTextboxAttribs( $name, $customAttribs, $this->context->getUser() );

		// div to set the cursor style see T156483
		$wikiText = '<div>' . $wikiText . '</div>';
		return Html::rawElement( 'div', $attribs, $wikiText );
	}

	/**
	 * Build HTML for a hidden textbox with the marked up foreign text.
	 *
	 * @param string $wikiText
	 * @return string
	 */
	private function buildHiddenChangesTextbox( $wikiText ) {
		$name = 'mw-twocolconflict-hidden-editor';

		$customAttribs['class'] = $name;
		if ( $this->wikiEditorIsEnabled() ) {
			$customAttribs['class'] .= ' mw-twocolconflict-wikieditor';
		}

		$attribs = $this->buildTextboxAttribs( $name, $customAttribs, $this->context->getUser() );

		return Html::rawElement( 'div', $attribs, $wikiText );
	}

	/**
	 * Build HTML for Edit Summary.
	 *
	 * @return string
	 */
	private function buildEditSummary() {
		$currentRev = $this->getArticle()->getPage()->getRevision();
		$baseRevId = $this->getContext()->getRequest()->getIntOrNull( 'editRevId' );
		$nEdits = $this->getTitle()->countRevisionsBetween( $baseRevId, $currentRev, 100 );

		if ( $nEdits === 0 ) {
			$out = '<div class="mw-twocolconflict-edit-summary">';
			$out .= Linker::userLink( $currentRev->getUser(), $currentRev->getUserText() );
			$out .= $this->getContext()->getLanguage()->getDirMark();
			$out .= Linker::revComment( $currentRev );
			$out .= '</div>';
		} else {
			$services = MediaWikiServices::getInstance();
			$linkRenderer = $services->getLinkRenderer();
			$historyLinkHtml = $linkRenderer->makeKnownLink(
				$this->getTitle(),
				$this->getContext()->msg( 'twoColConflict-history-link' )->text(),
				[
					'target' => '_blank',
				],
				[
					'action' => 'history',
				]
			);

			$out = $this->getContext()->msg(
				'twoColConflict-changes-col-desc-3',
				$nEdits + 1,
				$historyLinkHtml
			)->text();
		}

		return $out;
	}

	/**
	 * Build HTML to encapsulate editor with the conflicting text.
	 *
	 * @return string
	 */
	private function buildConflictPageEditorCol() {
		$out = '<div class="mw-twocolconflict-col-header">';
		$out .= '<h3>' . $this->getContext()->msg( 'twoColConflict-editor-col-title' ) . '</h3>';
		$out .= '<div class="mw-twocolconflict-col-desc">';
		$out .= '<div class="mw-twocolconflict-edit-desc">';
		$out .= '<p>' . $this->getContext()->msg( 'twoColConflict-editor-col-desc-1' ) . '</p>';
		$out .= '<p>'
			. $this->getContext()->msg( 'twoColConflict-editor-col-desc-2', $this->getSubmitButtonLabel() ) . '</p>';
		$out .= '</div>';
		$out .= '<ol class="mw-twocolconflict-base-selection-desc">';
		$out .= '<li>' . $this->getContext()->msg( 'twoColConflict-base-selection-desc-1' ) . '</li>';
		$out .= '<li>' . $this->getContext()->msg( 'twoColConflict-base-selection-desc-2' ) . '</li>';
		$out .= '<li>'
			. $this->getContext()->msg( 'twoColConflict-base-selection-desc-3', $this->getSubmitButtonLabel() ) . '</li>';
		$out .= '</ol></div></div>';

		return $out;
	}

	/**
	 * Build HTML for the hidden field with the text the user submitted.
	 *
	 * @return string
	 */
	private function buildMyVersionTextHiddenField() {
		$editableMyVersionText = $this->toEditText( $this->textbox1 );
		return HTML::input( 'mw-twocolconflict-mytext', $editableMyVersionText, 'hidden' );
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
	 * Get unified diff from the conflicting texts
	 *
	 * @return array[]
	 */
	private function getUnifiedDiff() {
		$currentText = $this->toEditText( $this->getCurrentContent() );
		$yourText = $this->textbox1;

		$currentLines = explode( "\n", $currentText );
		$yourLines = explode( "\n", str_replace( "\r\n", "\n", $yourText ) );

		return $this->getLineBasedUnifiedDiff( $currentLines, $yourLines );
	}

	/**
	 * Build HTML for the content of the unified diff box.
	 *
	 * @param array[] $unifiedDiff
	 * @return string
	 */
	private function getMarkedUpDiffText( array $unifiedDiff ) {
		$lastUser = $this->getArticle()->getPage()->getUserText();

		$output = '';
		foreach ( $unifiedDiff as $key => $currentLine ) {
			foreach ( $currentLine as $changeSet ) {
				switch ( $changeSet['action'] ) {
					case 'add':
						$class = 'mw-twocolconflict-diffchange-own';
						if ( $this->hasConflictInLine( $currentLine ) ) {
							$class .= ' mw-twocolconflict-diffchange-conflict';
						}

						$output .= '<div class="' . $class . '">' .
							'<div class="mw-twocolconflict-diffchange-title">' .
							'<span mw-twocolconflict-diffchange-title-pseudo="' .
							$this->context->msg( 'twoColConflict-diffchange-own-title' )->escaped() .
							// unselectable used by IE9
							'" unselectable="on">' .
							'</span>' .
							'</div>' .
							$changeSet['new'] .
							'</div>' . "\n";
						break;
					case 'delete':
						$class = 'mw-twocolconflict-diffchange-foreign';
						if ( $this->hasConflictInLine( $currentLine ) ) {
							$class .= ' mw-twocolconflict-diffchange-conflict';
						}

						$output .= '<div class="' . $class . '">' .
							'<div class="mw-twocolconflict-diffchange-title">' .
							'<span mw-twocolconflict-diffchange-title-pseudo="' .
							$this->context->msg(
								'twoColConflict-diffchange-foreign-title',
								$lastUser
							)->escaped() .
							// unselectable used by IE9
							'" unselectable="on">' .
							'</span>' .
							'</div>' .
							$changeSet['old'] .
							'</div>';

						if ( !$this->hasConflictInLine( $currentLine ) ) {
							$output .= "\n";
						}
						break;
					case 'copy':
						$output .= '<div class="mw-twocolconflict-diffchange-same">' .
							$this->addUnchangedText( $changeSet['copy'] ) .
							'</div>' . "\n";
						break;
				}
			}
		}

		return $this->normalizeMarkedUpText( $output );
	}

	/**
	 * Build HTML for the marked up foreign text in the hidden textbox
	 *
	 * @param array[] $unifiedDiff
	 * @return string
	 */
	private function getMarkedUpForeignText( array $unifiedDiff ) {
		$output = '';
		foreach ( $unifiedDiff as $key => $currentLine ) {
			foreach ( $currentLine as $changeSet ) {
				switch ( $changeSet['action'] ) {
					case 'add':
						$class = 'mw-twocolconflict-plain-own';
						if ( $this->hasConflictInLine( $currentLine ) ) {
							$class .= ' mw-twocolconflict-plain-own';
						}

						$output .= '<div class="' . $class . '"></div>';
						break;
					case 'delete':
						$class = 'mw-twocolconflict-plain-foreign';
						if ( $this->hasConflictInLine( $currentLine ) ) {
							$class .= ' mw-twocolconflict-plain-conflict';
						}

						$output .= '<div class="' . $class . '">' .
							$changeSet['old'] . "\n" .
							'</div>';
						break;
					case 'copy':
						$output .= '<div class="mw-twocolconflict-plain-same">' .
							$changeSet['copy'] . "\n" .
							'</div>';
						break;
				}
			}
		}

		return $this->normalizeMarkedUpText( $output );
	}

	/**
	 * Check if a unified diff line contains an edit conflict.
	 *
	 * @param array[] $currentLine
	 * @return boolean
	 */
	private function hasConflictInLine( array $currentLine ) {
		if ( count( $currentLine ) < 2 ) {
			return false;
		}

		return $currentLine[0]['action'] === 'delete' &&
			$currentLine[1]['action'] === 'add';
	}

	/**
	 * Normalize marked up lines to editor text.

	 * @param string $wikiText
	 * @return string
	 */
	private function normalizeMarkedUpText( $wikiText ) {
		$wikiText = $this->safeUnicodeOutput( $wikiText );
		return nl2br( $this->addNewLineAtEnd( $wikiText ) );
	}

	/**
	 * Build HTML for the unchanged text in the unified diff box.
	 * @param string $text HTML
	 * @return string HTML
	 */
	private function addUnchangedText( $text ) {
		$collapsedText = $this->getCollapsedText( $text );

		if ( !$collapsedText ) {
			return $text;
		}

		return
			'<div class="mw-twocolconflict-diffchange-same-full">' . $text . '</div>' .
			'<div class="mw-twocolconflict-diffchange-same-collapsed">' . $collapsedText . '</div>';
	}

	/**
	 * Get a collapsed version of multi-line text.
	 * Returns false if text is within length-limit.
	 *
	 * @param string $text HTML
	 * @param int $maxLength
	 * @return string|false
	 */
	private function getCollapsedText( $text, $maxLength = 150 ) {
		$text = $this->trimWhiteSpaces( html_entity_decode( $text ) );
		$lines = explode( "\n", $text );

		if ( mb_strlen( $text ) <= $maxLength && count( $lines ) <= 2 ) {
			return false;
		}

		return
			'<span class="mw-twocolconflict-diffchange-fadeout-end">' .
			htmlspecialchars( $this->trimStringToFullWord( $lines[0], $maxLength / 2, true ) ) .
			'</span>' .
			( count( $lines ) > 1 ? "\n" : $this->getContext()->msg( 'word-separator' ) ) .
			'<span class="mw-twocolconflict-diffchange-fadeout-start">' .
			htmlspecialchars( $this->trimStringToFullWord( array_pop( $lines ), $maxLength / 2, false ) ) .
			'</span>';
	}

	/**
	 * Trims a string at the start or end to the next full word.
	 *
	 * @param string $string
	 * @param int $maxLength
	 * @param boolean $trimAtEnd
	 * @return string
	 */
	private function trimStringToFullWord( $string, $maxLength, $trimAtEnd = true ) {
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

		return $this->trimWhiteSpaces( $result, $trimAtEnd );
	}

	/**
	 * Trims whitespaces and most non-printable characters from a string.
	 *
	 * @param string $string
	 * @param null|boolean $trimAtEnd
	 * @return string
	 */
	private function trimWhiteSpaces( $string, $trimAtEnd = null ) {
		if ( $trimAtEnd !== false ) {
			$string = preg_replace( '/[' . self::WHITESPACES . ']+$/u', '', $string );
		}

		if ( $trimAtEnd !== true ) {
			$string = preg_replace( '/^[' . self::WHITESPACES . ']+/u', '', $string );
		}

		return $string;
	}

	private function wikiEditorIsEnabled() {
		return class_exists( WikiEditorHooks::class ) && WikiEditorHooks::isEnabled( 'toolbar' );
	}

	private function deactivateWikEd() {
		// T167503, T168503 might be removed when wikEd works with TwoColConflict
		$this->context->getOutput()->addMeta( 'wikEdStartupFlag', '' );
	}

	private function addCSS() {
		$this->context->getOutput()->addModuleStyles( [
			'ext.TwoColConflict.editor',
			'ext.TwoColConflict.HelpDialogCss',
		] );
	}

	private function addJS() {
		$this->context->getOutput()->addJsConfigVars( 'wgTwoColConflict', 'true' );
		$this->context->getOutput()->addJsConfigVars( 'wgTwoColConflictWikiEditor', $this->wikiEditorIsEnabled() );
		$this->context->getOutput()->addJsConfigVars( 'wgTwoColConflictSubmitLabel', $this->getSubmitButtonLabel() );

		$this->context->getOutput()->addModules( [
			'ext.TwoColConflict.initJs',
			'ext.TwoColConflict.filterOptionsJs'
		] );
	}
}
