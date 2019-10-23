const Page = require( 'wdio-mediawiki/Page' ),
	EditPage = require( '../pageobjects/edit.page' ),
	BetaPreferencesPage = require( '../pageobjects/betapreferences.page' ),
	UserLoginPage = require( 'wdio-mediawiki/LoginPage' ),
	Api = require( 'wdio-mediawiki/Api' ),
	Util = require( 'wdio-mediawiki/Util' );

class EditConflictPage extends Page {
	get conflictHeader() { return browser.element( '.mw-twocolconflict-split-header' ); }
	get conflictView() { return browser.element( '.mw-twocolconflict-split-view' ); }

	getParagraph( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-editable' ); }
	getEditButton( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-edit-button' ); }
	getSaveButton( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-save-button' ); }
	getResetButton( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-reset-button' ); }
	getEditor( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-editor' ); }
	getDiffText( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-difftext' ); }
	getEditDisabledEditButtonPopup( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-disabled-edit-button-popup' ); }

	get yourParagraphSelection() { return browser.element( '.mw-twocolconflict-split-selection div:nth-child(2) span' ); }
	get resetConfirmationPopup() { return browser.element( '.oo-ui-window-content' ); }
	get resetConfirmationButton() { return browser.element( '.oo-ui-window-content .oo-ui-messageDialog-actions span:nth-of-type(2) a' ); }

	get collapsedParagraph() { return browser.element( '.mw-twocolconflict-split-collapsed' ); }
	get expandedParagraph() { return browser.element( '.mw-twocolconflict-split-expanded' ); }
	get fadeOverlay() { return browser.element( '.mw-twocolconflict-split-fade' ); }
	get collapseButton() { return browser.element( '.mw-twocolconflict-split-collapse-button' ); }
	get expandButton() { return browser.element( '.mw-twocolconflict-split-expand-button' ); }

	get infoButton() { return browser.element( '.mw-twocolconflict-split-tour-help-button' ); }
	get tourDialog() { return browser.element( '.mw-twocolconflict-split-tour-intro-container' ); }
	get tourDialogCloseButton() { return browser.element( '.mw-twocolconflict-split-tour-intro-container a' ); }

	get tourDiffChangeButton() { return browser.element( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-pulsating-button' ); }
	get tourSplitSelectionButton() { return browser.element( '.mw-twocolconflict-split-selection .mw-twocolconflict-split-tour-pulsating-button' ); }
	get tourYourVersionHeaderButton() { return browser.element( '.mw-twocolconflict-split-your-version-header .mw-twocolconflict-split-tour-pulsating-button' ); }

	get tourDiffChangePopup() { return browser.element( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-popup' ); }
	get tourDiffChangePopupCloseButton() { return browser.element( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-popup a' ); }

	get submitButton() { return browser.element( '#wpSave' ); }
	get previewButton() { return browser.element( '#wpPreview' ); }
	get diffButton() { return browser.element( '#wpDiff' ); }

	get previewView() { return browser.element( '#wikiPreview' ); }
	get previewText() { return browser.element( '#wikiPreview .mw-parser-output' ); }

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

	enforceSplitEditConflict() {
		return browser.setCookie( {
			name: 'mw-twocolconflict-split-ui',
			value: '1'
		} );
	}

	/**
	 * @param {boolean} [show] Defaults to true.
     * @return {Promise} Promise from the mw.Api request
	 */
	toggleHelpDialog( show ) {
		var hide = show === false;
		Util.waitForModuleState( 'mediawiki.base' );

		return browser.execute( function ( hide ) {
			/* global mw */
			return mw.loader.using( 'mediawiki.api' ).then( function () {
				return new mw.Api().saveOption(
					'userjs-twocolconflict-hide-help-dialogue',
					hide ? '1' : '0'
				);
			} );
		}, hide );
	}

	prepareEditConflict( conflictUser, conflictUserPassword ) {
		browser.call( function () {
			return Api.createAccount( conflictUser, conflictUserPassword );
		} );
		UserLoginPage.loginAdmin();
		BetaPreferencesPage.enableTwoColConflictBetaFeature();
		this.toggleHelpDialog( false );
		this.enforceSplitEditConflict();

		browser.execute( function () {
			return mw.loader.using( 'mediawiki.api' ).then( function () {
				return new mw.Api().saveOptions( {
					'visualeditor-hidebetawelcome': '1',
					'visualeditor-betatempdisable': '1'
				} );
			} );
		} );
	}

	showSimpleConflict( conflictUser, conflictUserPassword ) {
		this.createConflict(
			conflictUser,
			conflictUserPassword,
			'Line1\nLine2',
			'Line1\nChange <span lang="de">A</span>',
			'Line1\nChange <span lang="en">B</span>'
		);
	}

	showBigConflict( conflictUser, conflictUserPassword ) {
		this.createConflict(
			conflictUser,
			conflictUserPassword,
			'Line1\nLine2\nLine3\nline4',
			'Line1\nLine2\nLine3\nChange <span lang="de">A</span>',
			'Line1\nLine2\nLine3\nChange <span lang="en">B</span>'
		);
	}

	createConflict( conflictUser, conflictUserPassword, startText, otherText, yourText ) {
		const title = Util.getTestString( 'conflict-title-' );

		browser.call( function () {
			return Api.edit(
				title,
				startText
			);
		} );

		browser.pause( 500 ); // make sure Api edit is finished

		EditPage.openForEditing( title );
		EditPage.content.waitForExist();

		browser.call( function () {
			return Api.edit(
				title,
				otherText,
				conflictUser,
				conflictUserPassword
			);
		} );
		browser.pause( 500 ); // make sure Api edit is finished

		EditPage.content.setValue( yourText );
		EditPage.save.click();

		this.infoButton.waitForVisible( 60000 ); // JS for the tour is loaded
	}
}

module.exports = new EditConflictPage();
