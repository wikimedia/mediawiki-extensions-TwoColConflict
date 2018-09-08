<?php

namespace TwoColConflict\InlineTwoColConflict;

use Html;
use InvalidArgumentException;
use Linker;
use MediaWiki\EditPage\TextboxBuilder;
use MediaWiki\EditPage\TextConflictHelper;
use MediaWiki\MediaWikiServices;
use OOUI\ButtonInputWidget;
use OOUI\FieldsetLayout;
use OOUI\RadioSelectInputWidget;
use Revision;
use Title;
use TwoColConflict\CollapsedTextBuilder;
use TwoColConflict\LineBasedUnifiedDiffFormatter;
use WikiPage;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class InlineTwoColConflictHelper extends TextConflictHelper {

	/**
	 * @var Revision
	 */
	private $revision;

	/**
	 * @inheritDoc
	 */
	public function __construct( Title $title, \OutputPage $out, \IBufferingStatsdDataFactory $stats,
		$submitLabel
	) {
		parent::__construct( $title, $out, $stats, $submitLabel );

		$wikiPage = WikiPage::factory( $title );
		/** @see https://phabricator.wikimedia.org/T203085 */
		$wikiPage->loadPageData( 'fromdbmaster' );
		$this->revision = $wikiPage->getRevision();

		if ( !$this->revision ) {
			throw new InvalidArgumentException( 'The title "' . $title->getPrefixedText() .
				'" does not refer to an existing page' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function incrementConflictStats() {
		parent::incrementConflictStats();
		$this->stats->increment( 'TwoColConflict.conflict' );
		// XXX This is copied directly from core and we may be able to refactor something here.
		// Only include 'standard' namespaces to avoid creating unknown numbers of statsd metrics
		if (
			$this->title->getNamespace() >= NS_MAIN &&
			$this->title->getNamespace() <= NS_CATEGORY_TALK
		) {
			$this->stats->increment(
				'TwoColConflict.conflict.byNamespaceId.' . $this->title->getNamespace()
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function incrementResolvedStats() {
		parent::incrementResolvedStats();
		$this->stats->increment( 'TwoColConflict.conflict.resolved' );
		// XXX This is copied directly from core and we may be able to refactor something here.
		// Only include 'standard' namespaces to avoid creating unknown numbers of statsd metrics
		if (
			$this->title->getNamespace() >= NS_MAIN &&
			$this->title->getNamespace() <= NS_CATEGORY_TALK
		) {
			$this->stats->increment(
				'TwoColConflict.conflict.resolved.byNamespaceId.' . $this->title->getNamespace()
			);
		}
	}

	/**
	 * Replace default header for explaining the conflict screen.
	 *
	 * @return string
	 */
	public function getExplainHeader() {
		// don't show conflict message when coming from VisualEditor
		if ( $this->out->getRequest()->getVal( 'veswitched' ) !== "1" ) {
			return Html::rawElement(
				'div',
				[ 'class' => 'mw-twocolconflict-explainconflict warningbox' ],
				$this->out->msg(
					'twocolconflict-explainconflict',
					$this->out->msg( $this->submitLabel )->text()
				)->parse()
			);
		} else {
			return '';
		}
	}

	/**
	 * Shows the diff part in the original conflict handling. Is not
	 * used and overwritten.
	 */
	public function showEditFormTextAfterFooters() {
	}

	/**
	 * Build HTML that will be added before the default edit form.
	 *
	 * @return string
	 */
	public function getEditFormHtmlBeforeContent() {
		$out = Html::input( 'mw-twocolconflict-submit', 'true', 'hidden' );
		$out .= Html::input(
			'mw-twocolconflict-title',
			$this->title->getText(), 'hidden'
		);
		$out .= $this->buildConflictPageChangesCol();

		$editorClass = '';
		if ( $this->wikiEditorIsEnabled() ) {
			$editorClass = ' mw-twocolconflict-wikieditor';
		}
		$out .= '<div class="mw-twocolconflict-editor-col' . $editorClass . '">';
		$out .= $this->buildConflictPageEditorCol();
		$out .= $this->buildRawTextsHiddenFields();

		return $out;
	}

	/**
	 * Build HTML content that will be added after the default edit form.
	 *
	 * @return string
	 */
	public function getEditFormHtmlAfterContent() {
		// Use this time to add all of our stuff to OutputPage
		$this->addCSS();
		$this->addJS();
		$this->deactivateWikEd();

		// this div is opened when encapsulating the default editor in getEditFormTextBeforeContent.
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
		$out .= '<h3 id="mw-twocolconflict-changes-header">' .
			$this->out->msg( 'twocolconflict-changes-col-title' )->parse() . '</h3>';
		$out .= '<div class="mw-twocolconflict-col-desc">';
		$out .= $this->out->msg( 'twocolconflict-changes-col-desc-1' )->text();
		$out .= '<ul>';
		$out .= '';
		$out .= '<li><span class="mw-twocolconflict-lastuser">' .
			$this->out->msg( 'twocolconflict-changes-col-desc-2' )->text() .
			'</span><br/>' . $this->buildEditSummary() . '</li>';
		$out .= '<li><span class="mw-twocolconflict-user">' .
			$this->out->msg( 'twocolconflict-changes-col-desc-4' )->text() .
			'</span></li>';
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
		$this->out->enableOOUI();

		$showHideOptions = new RadioSelectInputWidget( [
			'name' => 'mw-twocolconflict-same',
			'classes' => [ 'mw-twocolconflict-filter-options-btn' ],
			'options' => [
				[
					'data' => 'show',
					'label' => $this->out->msg( 'twocolconflict-label-show' )->text()
				],
				[
					'data' => 'hide',
					'label' => $this->out->msg( 'twocolconflict-label-hide' )->text()
				],
			],
		] );

		$fieldset = new FieldsetLayout();
		$fieldset->addItems( [
			$showHideOptions
		] );

		$out = '<div class="mw-twocolconflict-filter-options-container">';

		$out .= '<div class="mw-twocolconflict-filter-options-row">';
		$out .= '<div class="mw-twocolconflict-filter-titles">' .
			$this->out->msg( 'twocolconflict-label-unchanged' )->text() .
			'</div>';
		$out .= $fieldset;
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
		$this->out->addModuleStyles( 'oojs-ui.styles.icons-content' );

		$helpButton = new ButtonInputWidget( [
			'icon' => 'help',
			'framed' => false,
			'name' => 'mw-twocolconflict-show-help',
			'title' => $this->out->msg( 'twocolconflict-show-help-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-show-help' ]
		] );
		$helpButton->setAttributes( [
			'aria-haspopup' => 'true',
			'aria-label' => $helpButton->getTitle()
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
	 *
	 * @return string
	 */
	private function buildChangesTextbox( $wikiText ) {
		$name = 'mw-twocolconflict-changes-editor';

		$customAttribs = [
			'tabindex' => 1,
			'accesskey' => '.',
			'class' => $name,
			'aria-labelledby' => 'mw-twocolconflict-changes-header',
			'role' => 'grid',
		];
		if ( $this->wikiEditorIsEnabled() ) {
			$customAttribs['class'] .= ' mw-twocolconflict-wikieditor';
		}

		$attribs = ( new TextboxBuilder() )->buildTextboxAttribs(
			$name, $customAttribs, $this->out->getUser(), $this->title
		);

		// div to set the cursor style see T156483
		$wikiText = '<div>' . $wikiText . '</div>';
		return Html::rawElement( 'div', $attribs, $wikiText );
	}

	/**
	 * Build HTML for a hidden textbox with the marked up foreign text.
	 *
	 * @param string $wikiText
	 *
	 * @return string
	 */
	private function buildHiddenChangesTextbox( $wikiText ) {
		$name = 'mw-twocolconflict-hidden-editor';

		$customAttribs = [ 'class' => $name ];
		if ( $this->wikiEditorIsEnabled() ) {
			$customAttribs['class'] .= ' mw-twocolconflict-wikieditor';
		}

		$attribs = ( new TextboxBuilder() )->buildTextboxAttribs(
			$name, $customAttribs, $this->out->getUser(), $this->title
		);

		return Html::rawElement( 'div', $attribs, $wikiText );
	}

	/**
	 * Build HTML for Edit Summary.
	 *
	 * @return string
	 */
	protected function buildEditSummary() {
		$currentRev = $this->revision;
		$baseRevId = $this->out->getRequest()->getIntOrNull( 'editRevId' );
		$nEdits = $this->title->countRevisionsBetween( $baseRevId, $currentRev, 100 );

		if ( $nEdits === 0 ) {
			$out = '<div class="mw-twocolconflict-edit-summary">';
			$out .= Linker::userLink( $currentRev->getUser(), $currentRev->getUserText() );
			$out .= $this->out->getLanguage()->getDirMark();
			$out .= Linker::revComment( $currentRev );
			$out .= '</div>';
		} else {
			$services = MediaWikiServices::getInstance();
			$linkRenderer = $services->getLinkRenderer();
			$historyLinkHtml = $linkRenderer->makeKnownLink(
				$this->title,
				$this->out->msg( 'twocolconflict-history-link' )->text(),
				[
					'target' => '_blank',
				],
				[
					'action' => 'history',
				]
			);

			$out = $this->out->msg(
				'twocolconflict-changes-col-desc-3',
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
		$out .= '<h3 id="mw-twocolconflict-edit-header">' .
			$this->out->msg( 'twocolconflict-editor-col-title' ) . '</h3>';
		$out .= '<div class="mw-twocolconflict-col-desc">';
		$out .= '<div class="mw-twocolconflict-edit-desc">';
		$out .= '<p>' . $this->out->msg( 'twocolconflict-editor-col-desc-1' ) . '</p>';
		$submitLabel = $this->out->msg( $this->submitLabel )->text();
		$out .= '<p>' .
			$this->out->msg(
				'twocolconflict-editor-col-desc-2', $submitLabel
			) . '</p>';
		$out .= '</div>';
		$out .= '<ol class="mw-twocolconflict-base-selection-desc">';
		$out .= '<li>' . $this->out->msg( 'twocolconflict-base-selection-desc-1' ) .
			'</li>';
		$out .= '<li>' . $this->out->msg( 'twocolconflict-base-selection-desc-2' ) .
			'</li>';
		$out .= '<li>'
			. $this->out->msg(
				'twocolconflict-base-selection-desc-3', $submitLabel
			) . '</li>';
		$out .= '</ol></div></div>';

		return $out;
	}

	/**
	 * Build HTML for the hidden field with the text the user submitted.
	 *
	 * @return string
	 */
	protected function buildRawTextsHiddenFields() {
		return Html::input( 'mw-twocolconflict-your-text', $this->yourtext, 'hidden' ) .
			Html::input( 'mw-twocolconflict-current-text', $this->storedversion, 'hidden' );
	}

	/**
	 * Get array with line based diff changes.
	 *
	 * @param string[] $fromTextLines
	 * @param string[] $toTextLines
	 *
	 * @return array[]
	 */
	protected function getLineBasedUnifiedDiff( $fromTextLines, $toTextLines ) {
		$formatter = new LineBasedUnifiedDiffFormatter();
		$formatter->insClass = ' class="mw-twocolconflict-diffchange"';
		$formatter->delClass = ' class="mw-twocolconflict-diffchange"';

		return $formatter->format(
			new \Diff( $fromTextLines, $toTextLines )
		);
	}

	/**
	 * Get unified diff from the conflicting texts
	 *
	 * @return array[]
	 */
	protected function getUnifiedDiff() {
		$currentLines = explode( "\n", $this->storedversion );
		$yourLines = explode( "\n", str_replace( "\r\n", "\n", $this->yourtext ) );

		return $this->getLineBasedUnifiedDiff( $currentLines, $yourLines );
	}

	/**
	 * @return string
	 */
	protected function getLastUserText() {
		return $this->revision->getUserText();
	}

	/**
	 * Build HTML for the content of the unified diff box.
	 *
	 * @param array[] $unifiedDiff
	 *
	 * @return string
	 */
	private function getMarkedUpDiffText( array $unifiedDiff ) {
		$lastUser = $this->getLastUserText();

		$output = '';
		foreach ( $unifiedDiff as $key => $currentLine ) {
			foreach ( $currentLine as $changeSet ) {
				switch ( $changeSet['action'] ) {
					case 'add':
						$class = 'mw-twocolconflict-diffchange-own';
						if ( $this->hasConflictInLine( $currentLine ) ) {
							$class .= ' mw-twocolconflict-diffchange-conflict';
						}
						$label = $this->out->msg( 'twocolconflict-diffchange-own-title' )->escaped();

						$output .= '<div class="' . $class . '" aria-label="' . $label . '" tabindex="1">' .
							'<div class="mw-twocolconflict-diffchange-title">' .
							'<span mw-twocolconflict-diffchange-title-pseudo="' .
							$label .
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
						$label = $this->out->msg(
							'twocolconflict-diffchange-foreign-title',
							$lastUser
						)->escaped();

						$output .= '<div class="' . $class . '" aria-label="' . $label . '" tabindex="1">' .
							'<div class="mw-twocolconflict-diffchange-title">' .
							'<span mw-twocolconflict-diffchange-title-pseudo="' .
							$label .
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
						$class = 'mw-twocolconflict-diffchange-same';
						$label = $this->out->msg( 'twocolconflict-diffchange-unchanged-title' )->escaped();
						$output .= '<div class="' . $class . '" aria-label="' . $label . '" tabindex="1">' .
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
	 *
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
	 *
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
	 *
	 * @param string $wikiText
	 *
	 * @return string
	 */
	private function normalizeMarkedUpText( $wikiText ) {
		return nl2br( ( new TextboxBuilder() )->addNewLineAtEnd( $wikiText ) );
	}

	/**
	 * Build HTML for the unchanged text in the unified diff box.
	 *
	 * @param string $text HTML
	 *
	 * @return string HTML
	 */
	private function addUnchangedText( $text ) {
		$collapsedText = CollapsedTextBuilder::buildCollapsedText( $text );

		if ( !$collapsedText ) {
			return $text;
		}

		return '<span class="mw-twocolconflict-diffchange-same-full">' . $text . '</span>' .
			'<span class="mw-twocolconflict-diffchange-same-collapsed">' . $collapsedText . '</span>';
	}

	private function wikiEditorIsEnabled() {
		return \ExtensionRegistry::getInstance()->isLoaded( 'WikiEditor' ) &&
			$this->out->getUser()->getOption( 'usebetatoolbar' );
	}

	private function deactivateWikEd() {
		// T167503, T168503 might be removed when wikEd works with TwoColConflict
		$this->out->addMeta( 'wikEdStartupFlag', '' );
	}

	private function addCSS() {
		$this->out->addModuleStyles( [
			'ext.TwoColConflict.InlineCss',
			'ext.TwoColConflict.Inline.HelpDialogCss',
		] );
	}

	private function addJS() {
		$this->out->addJsConfigVars( 'wgTwoColConflict', 'true' );
		$this->out->addJsConfigVars( 'wgTwoColConflictWikiEditor', $this->wikiEditorIsEnabled() );
		$this->out->addJsConfigVars( 'wgTwoColConflictSubmitLabel',
			$this->out->msg( $this->submitLabel )->text()
		);
		$this->out->addBodyClasses( [ 'mw-twocolconflict-page' ] );

		$this->out->addModules( [
			'ext.TwoColConflict.Inline.initJs',
			'ext.TwoColConflict.Inline.filterOptionsJs'
		] );
	}

}
