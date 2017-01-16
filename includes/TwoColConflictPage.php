<?php

/**
 * @license GNU GPL v2+
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class TwoColConflictPage extends EditPage {

	const WHITESPACES =
		'\s\xA0\x{1680}\x{180E}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}';

	/**
	 * Replace default header for explaining the conflict screen.
	 *
	 * @param OutputPage $out
	 */
	protected function addExplainConflictHeader( OutputPage $out ) {
		$labelAsPublish = $this->context->getConfig()->get(
			'EditSubmitButtonLabelPublish'
		);

		$buttonLabel = $this->context->msg(
			$labelAsPublish ? 'publishchanges' : 'savechanges'
		)->text();

		$out->wrapWikiMsg(
			"<div class='mw-twocolconflict-explainconflict warningbox'>\n$1\n</div>",
			[ 'twoColConflict-explainconflict', $buttonLabel ]
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
			$this->addJS();
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
		$out = $this->buildConflictPageChangesCol();

		$editorClass = '';
		if ( $this->wikiEditorIsEnabled() ) {
			$editorClass = ' mw-twocolconflict-wikieditor';
		}
		$out.= '<div class="mw-twocolconflict-editor-col' . $editorClass . '">';
		$out.= $this->buildConflictPageEditorCol();

		return $out;
	}

	/**
	 * Build HTML content that will be added after the default edit form.
	 *
	 * @return string
	 */
	private function addEditFormAfterContent() {
		// this div is opened when encapsulating the default editor in addEditFormBeforeContent.
		return '</div><div style="clear: both" />';
	}

	/**
	 * Build HTML that will add the textbox with the unified diff.
	 *
	 * @return string
	 */
	private function buildConflictPageChangesCol() {
		$currentUser = $this->context->getUser();

		$lastUser =
			'<span class="mw-twocolconflict-lastuser">' .
			$this->mArticle->getPage()->getUserText() .
			'</span>';
		$lastChangeTime = $this->getContext()->getLanguage()->userTimeAndDate(
			$this->getArticle()->getPage()->getTimestamp(),
			$currentUser
		);
		$yourChangeTime = $this->getContext()->getLanguage()->userTimeAndDate(
			time(),
			$currentUser
		);

		$out = '<div class="mw-twocolconflict-changes-col">';
		$out.= '<h3>' . $this->getContext()->msg( 'twoColConflict-changes-col-title' )->parse() .
			'</h3>';
		$out.= '<div class="mw-twocolconflict-col-desc">' . $this->getContext()->msg(
			'twoColConflict-changes-col-desc', $lastUser, $lastChangeTime, $yourChangeTime
			)->parse() . '</div>';
		$out.= $this->buildFilterOptionsMenu();
		$out.= $this->buildChangesTextbox();
		$out.= '</div>';

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
		] );

		$out = '<div class="mw-twocolconflict-filter-options">';
		$out.= '<div class="mw-twocolconflict-filter-unchanged">' .
			$this->getContext()->msg( 'twoColConflict-label-unchanged' ) .
			'</div>';
		$out.= $showHideOptions;
		$out.= '</div>';

		return $out;
	}

	/**
	 * Build HTML for the textbox with the unified diff.
	 *
	 * @return string
	 */
	private function buildChangesTextbox() {
		$name = 'mw-twocolconflict-changes-editor';
		$wikitext = $this->safeUnicodeOutput( $this->getUnifiedDiffText() );
		$wikitext = $this->addNewLineAtEnd( $wikitext );

		$customAttribs = [
			'tabindex' => 0,
			'class' => 'mw-twocolconflict-changes-editor'
		];
		if ( $this->wikiEditorIsEnabled() ) {
			$customAttribs[ 'class' ] .= ' mw-twocolconflict-wikieditor';
		}

		$attribs = $this->buildTextboxAttribs( $name, $customAttribs, $this->context->getUser() );

		return Html::rawElement( 'div', $attribs, $wikitext );
	}

	/**
	 * Build HTML to encapsulate editor with the conflicting text.
	 *
	 * @return string
	 */
	private function buildConflictPageEditorCol() {
		$lastUser = $this->getArticle()->getPage()->getUserText();
		$lastChangeTime = $this->getArticle()->getPage()->getTimestamp();
		$lastChangeTime = $this->context->getLanguage()->userTimeAndDate(
			$lastChangeTime, $this->context->getUser()
		);

		$out = '<h3>' . $this->getContext()->msg( 'twoColConflict-editor-col-title' ) . '</h3>';
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
		$yourLines = explode( "\n", str_replace( "\r\n", "\n", $yourText ) );

		$combinedChanges = $this->getLineBasedUnifiedDiff( $currentLines, $yourLines );

		$output = [];
		foreach ( $currentLines as $key => $currentLine ) {
			++$key;
			if ( isset( $combinedChanges[ $key ] ) ) {
				foreach ( $combinedChanges[ $key ] as $changeSet ) {
					switch ( $changeSet[ 'action' ] ) {
						case 'add':
							$output[] = '<div class="mw-twocolconflict-diffchange-own">' .
								'<div class="mw-twocolconflict-diffchange-title">' .
							    '<span mw-twocolconflict-diffchange-title-pseudo="' .
								$this->context->msg( 'twoColConflict-diffchange-own-title' )->escaped() .
							    '" unselectable="on">' . // used by IE9
							    '</span>' .
								'</div>' .
								$changeSet['new'] .
								'</div>';
							break;
						case 'delete':
							$output[] = '<div class="mw-twocolconflict-diffchange-foreign">' .
								'<div class="mw-twocolconflict-diffchange-title">' .
							    '<span mw-twocolconflict-diffchange-title-pseudo="' .
								$this->context->msg(
									'twoColConflict-diffchange-foreign-title',
									$lastUser
								)->escaped() .
							    '" unselectable="on">' . // used by IE9
							    '</span>' .
								'</div>' .
								$changeSet['old'] .
								'</div>';
							break;
						case 'copy':
							$output[] = '<div class="mw-twocolconflict-diffchange-same">' .
								$this->addUnchangedText( $changeSet['copy'] ) .
								'</div>';
							break;
					}
				}
			}
		}

		return implode( "\n", $output );
	}

	/**
	 * Build HTML for the unchanged text in the unified diff box.
	 *
	 * @return string
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
	 * @param string $text
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
			$result =  preg_replace(
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
		if ( $trimAtEnd === null ) {
			$string = preg_replace( '/[' . self::WHITESPACES . ']+$/u', '', $string );
			return preg_replace( '/^[' . self::WHITESPACES . ']+/u', '', $string );
		}

		if ( $trimAtEnd === true ) {
			return preg_replace( '/[' . self::WHITESPACES . ']+$/u', '', $string );
		}

		if ( $trimAtEnd === false ) {
			return preg_replace( '/^[' . self::WHITESPACES . ']+/u', '', $string );
		}

	}

	private function wikiEditorIsEnabled() {
		return class_exists( WikiEditorHooks::class ) && WikiEditorHooks::isEnabled( 'toolbar' );
	}

	private function addCSS() {
		$this->context->getOutput()->addModuleStyles( 'ext.TwoColConflict.editor' );
	}

	private function addJS() {
		$this->context->getOutput()->addModuleScripts( 'ext.TwoColConflict.filter' );
	}
}
