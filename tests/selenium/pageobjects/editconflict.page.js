const Page = require( 'wdio-mediawiki/Page' ),
	EditPage = require( '../pageobjects/edit.page' ),
	PreferencesPage = require( '../pageobjects/preferences.page' ),
	UserLoginPage = require( 'wdio-mediawiki/LoginPage' ),
	Api = require( 'wdio-mediawiki/Api' ),
	TestAccounts = require( '../test_accounts' ),
	Util = require( 'wdio-mediawiki/Util' );

class EditConflictPage extends Page {
	get conflictHeader() { return $( '.mw-twocolconflict-split-header' ); }
	get conflictView() { return $( '.mw-twocolconflict-split-view' ); }

	getParagraph( column ) { return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-editable' ); }
	getEditButton( column ) { return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-edit-button' ); }
	getSaveButton( column ) { return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-save-button' ); }
	getResetButton( column ) { return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-reset-button' ); }
	getEditor( column ) { return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-editor' ); }
	getDiffText( column ) { return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-difftext' ); }
	getEditDisabledEditButtonPopup( column ) { return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-disabled-edit-button-popup' ); }

	get selectionLabel() { return $( '.mw-twocolconflict-split-selector-label span' ); }
	get otherParagraphSelection() { return $( '.mw-twocolconflict-split-selection div:nth-child(1) span' ); }
	get otherParagraphRadio() { return $( '.mw-twocolconflict-split-selection div:nth-child(1) input' ); }
	get yourParagraphSelection() { return $( '.mw-twocolconflict-split-selection div:nth-child(2) span' ); }
	get yourParagraphRadio() { return $( '.mw-twocolconflict-split-selection div:nth-child(2) input' ); }
	get resetConfirmationPopup() { return $( '.oo-ui-windowManager-floating .oo-ui-window-content' ); }
	get resetConfirmationButton() { return $( '.oo-ui-windowManager-floating .oo-ui-window-content .oo-ui-messageDialog-actions span:nth-of-type(2) a' ); }

	get collapsedParagraph() { return $( '.mw-twocolconflict-split-collapsed' ); }
	get expandedParagraph() { return $( '.mw-twocolconflict-split-expanded' ); }
	get fadeOverlay() { return $( '.mw-twocolconflict-split-fade' ); }
	get collapseButton() { return $( '.mw-twocolconflict-split-collapse-button' ); }
	get expandButton() { return $( '.mw-twocolconflict-split-expand-button' ); }

	get infoButton() { return $( '.mw-twocolconflict-split-tour-help-button' ); }
	get tourDialog() { return $( '.mw-twocolconflict-split-tour-intro-container' ); }
	get tourDialogCloseButton() { return $( '.mw-twocolconflict-split-tour-intro-container a' ); }

	get tourDiffChangeButton() { return $( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-pulsating-button' ); }
	get tourSplitSelectionButton() { return $( '.mw-twocolconflict-split-selection .mw-twocolconflict-split-tour-pulsating-button' ); }
	get tourYourVersionHeaderButton() { return $( '.mw-twocolconflict-split-your-version-header .mw-twocolconflict-split-tour-pulsating-button' ); }

	get tourDiffChangePopup() { return $( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-popup' ); }
	get tourDiffChangePopupCloseButton() { return $( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-popup a' ); }

	get submitButton() { return $( '#wpSave' ); }
	get previewButton() { return $( '#wpPreview' ); }
	get diffButton() { return $( '#wpDiff' ); }

	get previewView() { return $( '#wikiPreview' ); }
	get previewText() { return $( '#wikiPreview .mw-parser-output' ); }

	get wpTextbox2() { return $( '#wpTextbox2' ); }

	hoverEditButton( column ) {
		browser.moveToObject( this.columnToClass( column ) + ' .mw-twocolconflict-split-edit-button' );
	}

	columnToClass( column ) {
		switch ( column ) {
			case 'other':
				return '.mw-twocolconflict-split-delete';
			case 'your':
				return '.mw-twocolconflict-split-add';
			default:
				return '.mw-twocolconflict-split-copy';
		}
	}

	/**
	 * @param {boolean} [show] Defaults to true.
     * @return {Promise} Promise from the mw.Api request
	 */
	toggleHelpDialog( show ) {
		var hide = show === false;
		Util.waitForModuleState( 'mediawiki.base' );

		return browser.execute( function ( hide ) {
			return mw.loader.using( 'mediawiki.api' ).then( function () {
				return new mw.Api().saveOption(
					'userjs-twocolconflict-hide-help-dialogue',
					hide ? '1' : '0'
				);
			} );
		}, hide );
	}

	prepareEditConflict() {
		UserLoginPage.loginAdmin();
		PreferencesPage.disableEditWarning();
		PreferencesPage.shouldUseTwoColConflict( true );
		PreferencesPage.enableTwoColConflictBetaFeature();
		this.toggleHelpDialog( false );

		browser.execute( function () {
			return mw.loader.using( 'mediawiki.api' ).then( function () {
				return new mw.Api().saveOptions( {
					'visualeditor-hidebetawelcome': '1',
					'visualeditor-betatempdisable': '1'
				} );
			} );
		} );
	}

	showSimpleConflict() {
		this.createConflict(
			'Line1\nLine2',
			'Line1\nChange <span lang="de">A</span>',
			'Line1\nChange <span lang="en">B</span>'
		);
		this.waitForUiToLoad();
	}

	showBigConflict() {
		this.createConflict(
			'Line1\nLine2\nLine3\nline4',
			'Line1\nLine2\nLine3\nChange <span lang="de">A</span>',
			'Line1\nLine2\nLine3\nChange <span lang="en">B</span>'
		);
		this.waitForUiToLoad();
	}

	editPage( credentials, title, text ) {
		browser.call( function () {
			return Api.edit(
				title,
				text,
				credentials.username,
				credentials.password
			);
		} );
		browser.pause( 500 );
	}

	createConflict(
		startText,
		otherText,
		yourText,
		title = null,
		section = null
	) {
		title = ( title !== null ) ? title : Util.getTestString( 'conflict-title-' );

		this.editPage( TestAccounts.you, title, startText );

		if ( section !== null ) {
			EditPage.openSectionForEditing( title, section );
		} else {
			EditPage.openForEditing( title );
		}

		EditPage.content.waitForExist();

		this.editPage( TestAccounts.other, title, otherText );

		EditPage.content.setValue( yourText );
		EditPage.save.click();
	}

	waitForUiToLoad() {
		this.infoButton.waitForVisible( 60000 ); // JS for the tour is loaded
	}

	testNoJs() {
		return browser.setCookie( {
			name: 'mw-twocolconflict-test-nojs',
			value: '1'
		} );
	}

}

module.exports = new EditConflictPage();
