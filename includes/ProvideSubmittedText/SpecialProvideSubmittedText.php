<?php

namespace TwoColConflict\ProvideSubmittedText;

use Html;
use MediaWiki\EditPage\TextboxBuilder;
use ObjectCache;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;
use Title;
use TwoColConflict\TwoColConflictContext;
use UnlistedSpecialPage;

/**
 * Special page allows users to see their originally submitted text while they
 * encounter an edit conflict.
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SpecialProvideSubmittedText extends UnlistedSpecialPage {
	public function __construct() {
		parent::__construct( 'TwoColConflictProvideSubmittedText' );
	}

	/**
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		$this->setHeaders();
		$this->getOutput()->addModuleStyles( 'ext.TwoColConflict.SplitCss' );
		$this->getOutput()->enableOOUI();

		$titleDbKey = $this->getRequest()->getText( 'mw-twocolconflict-cache-title' );
		$title = Title::newFromDBkey( $titleDbKey );
		if ( !$title ) {
			// TODO: Return with a 404 ("Not Found") and show an error message
			return;
		}

		$this->getOutput()->setPageTitle(
			$this->msg( 'editconflict', $title->getPrefixedText() )
		);

		$textCache = new SubmittedTextCache( ObjectCache::getInstance( 'db-replicated' ) );
		$text = $textCache->fetchText(
			$titleDbKey,
			$this->getOutput()->getUser(),
			$this->getOutput()->getRequest()->getSessionId()
		);

		if ( !$text ) {
			// TODO Return with a 410 ("Gone") and show an error message
			return;
		}

		$out = $this->getHeaderHintsHtml( TwoColConflictContext::isUsedAsBetaFeature() );
		$out .= $this->getTextHeaderLabelHtml();
		$out .= $this->getTextAreaHtml( $text );
		$out .= $this->getFooterHtml();

		$this->getOutput()->addHTML( $out );
	}

	private function getHeaderHintsHtml( $isBetaFeature ) {
		$hintMsg = $isBetaFeature
			? 'twocolconflict-split-header-hint-beta'
			: 'twocolconflict-split-header-hint';

		$out = $this->getMessageBox(
			'twocolconflict-special-header-overview',
			'notice',
			'mw-twocolconflict-overview'
		);
		$out .= $this->getMessageBox( $hintMsg, 'notice' );

		return $out;
	}

	private function getTextHeaderLabelHtml() {
		$html = Html::element(
			'span',
			[ 'class' => 'mw-twocolconflict-revision-label' ],
			$this->msg( 'twocolconflict-split-your-version-header' )->text()
		);
		$html .= Html::element( 'br' );
		$html .= Html::element(
			'span',
			[],
			$this->msg( 'twocolconflict-special-not-saved' )->text()
		);

		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-special-your-version-header' ],
			$html
		);
	}

	private function getTextAreaHtml( $text ) {
		$builder = new TextboxBuilder();
		$attribs = $builder->mergeClassesIntoAttributes(
			[ 'mw-twocolconflict-submitted-text' ],
			[ 'readonly', 'tabindex' => 1 ]
		);

		$attribs = $builder->buildTextboxAttribs(
			'wpTextbox2',
			$attribs,
			$this->getUser(),
			$this->getPageTitle()
		);

		return Html::element( 'span', [], $this->msg( 'twocolconflict-special-textarea-hint' )->text() ) .
			Html::textarea(
				'wpTextbox2',
				$builder->addNewLineAtEnd( $text ),
				$attribs
			);
	}

	private function getFooterHtml() {
		return Html::element( 'p', [], $this->msg( 'twocolconflict-special-footer-hint' )->text() );
	}

	private function getMessageBox( string $messageKey, string $type, $classes = [] ) : string {
		$html = $this->msg( $messageKey )->parse();
		return ( new MessageWidget( [
			'label' => new HtmlSnippet( $html ),
			'type' => $type,
		] ) )
			->addClasses( array_merge( [ 'mw-twocolconflict-messageWidget' ], (array)$classes ) )
			->toString();
	}
}
