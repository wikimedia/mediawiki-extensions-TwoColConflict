const Page = require( 'wdio-mediawiki/Page' ),
	EditPage = require( '../../../../../tests/selenium/pageobjects/edit.page' ),
	BetaPreferencesPage = require( '../pageobjects/betapreferences.page' ),
	UserLoginPage = require( 'wdio-mediawiki/LoginPage' ),
	Api = require( 'wdio-mediawiki/Api' ),
	Util = require( 'wdio-mediawiki/Util' ),
	MWBot = require( 'mwbot' );

class EditConflictPage extends Page {
	get conflictHeader() { return browser.element( '.mw-twocolconflict-split-header' ); }
	get conflictView() { return browser.element( '.mw-twocolconflict-split-view' ); }

	getParagraph( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-editable' ); }
	getEditButton( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-edit-button' ); }
	getSaveButton( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-save-button' ); }
	getResetButton( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-reset-button' ); }
	getEditor( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-editor' ); }
	getDiffText( column ) { return browser.element( this.columnToClass( column ) + ' .mw-twocolconflict-split-difftext' ); }

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

	get tourDiffChangeButton() { return browser.element( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-still-button' ); }
	get tourSplitSelectionButton() { return browser.element( '.mw-twocolconflict-split-selection .mw-twocolconflict-split-tour-still-button' ); }
	get tourYourVersionHeaderButton() { return browser.element( '.mw-twocolconflict-split-your-version-header .mw-twocolconflict-split-tour-still-button' ); }

	get tourDiffChangePopup() { return browser.element( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-popup' ); }
	get tourDiffChangePopupCloseButton() { return browser.element( '.mw-twocolconflict-diffchange .mw-twocolconflict-split-tour-popup a' ); }

	get submitButton() { return browser.element( '#wpSave' ); }
	get previewButton() { return browser.element( '#wpPreview' ); }
	get diffButton() { return browser.element( '#wpDiff' ); }

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

		browser.pause( 300 ); // wait for mw JS to load

		return browser.execute( function ( hide ) {
			return ( new mediaWiki.Api() ).saveOption(
				'userjs-twocolconflict-hide-help-dialogue',
				hide ? '1' : '0'
			);
		}, hide );
	}

	prepareEditConflict() {
		UserLoginPage.loginAdmin();
		BetaPreferencesPage.enableTwoColConflictBetaFeature();
		this.toggleHelpDialog( false );
		this.enforceSplitEditConflict();
	}

	showSimpleConflict( conflictUser, conflictUserPassword ) {
		this.createConflict(
			Util.getTestString( 'conflict-title-' ),
			conflictUser,
			conflictUserPassword,
			'Line1\nLine2',
			'Line1\nChangeA',
			'Line1\nChangeB'
		);

		this.infoButton.waitForVisible( 60000 ); // JS for the tour is loaded
	}

	showBigConflict( conflictUser, conflictUserPassword ) {
		this.createConflict(
			Util.getTestString( 'conflict-title-' ),
			conflictUser,
			conflictUserPassword,
			'Line1\nLine2\nLine3\nline4',
			'Line1\nLine2\nLine3\nChangeA',
			'Line1\nLine2\nLine3\nChangeB'
		);

		this.infoButton.waitForVisible( 60000 ); // JS for the tour is loaded
	}

	createConflict( title, conflictUser, conflictUserPassword, startText, otherText, yourText ) {
		browser.call( function () {
			return Api.edit(
				title,
				startText
			);
		} );
		browser.pause( 300 ); // make sure Api edit is finished

		EditPage.openForEditing( title );

		browser.call( function () {
			let bot = new MWBot();

			return bot.loginGetEditToken( {
				apiUrl: `${browser.options.baseUrl}/api.php`,
				username: conflictUser,
				password: conflictUserPassword
			} ).then( function () {
				return bot.edit( title, otherText, `Changed content to "${otherText}"` );
			} );
		} );
		browser.pause( 300 ); // make sure bot edit is finished

		EditPage.content.setValue( yourText );
		EditPage.save.click();
	}
}

module.exports = new EditConflictPage();
