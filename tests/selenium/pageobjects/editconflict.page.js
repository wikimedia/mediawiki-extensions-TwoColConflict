'use strict';

const Page = require( 'wdio-mediawiki/Page' ),
	EditPage = require( '../pageobjects/edit.page' ),
	UserLoginPage = require( 'wdio-mediawiki/LoginPage' ),
	TestAccounts = require( '../test_accounts' ),
	Util = require( 'wdio-mediawiki/Util' );

class EditConflictPage extends Page {
	get conflictHeader() {
		return $( '.mw-twocolconflict-split-header' );
	}

	get conflictView() {
		return $( '.mw-twocolconflict-split-view' );
	}

	getParagraph( column ) {
		return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-editable' );
	}

	getEditButton( column ) {
		return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-edit-button' );
	}

	getSaveButton( column ) {
		return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-save-button' );
	}

	getResetButton( column ) {
		return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-reset-button' );
	}

	getEditor( column ) {
		return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-editor' );
	}

	getDiffText( column ) {
		return $( this.columnToClass( column ) + ' .mw-twocolconflict-split-difftext' );
	}

	getColumn( column ) {
		return $( this.columnToClass( column ) );
	}

	get otherParagraphSelection() {
		return $( '.mw-twocolconflict-split-selection-row div:nth-child(1) span' );
	}

	get otherParagraphRadio() {
		return $( '.mw-twocolconflict-split-selection-row div:nth-child(1) input' );
	}

	get yourParagraphSelection() {
		return $( '.mw-twocolconflict-split-selection-row div:nth-child(2) span' );
	}

	get yourParagraphRadio() {
		return $( '.mw-twocolconflict-split-selection-row div:nth-child(2) input' );
	}

	get resetConfirmationPopup() {
		return $( '.oo-ui-windowManager-floating .oo-ui-window-content' );
	}

	get resetConfirmationButton() {
		return $( '.oo-ui-windowManager-floating .oo-ui-window-content .oo-ui-messageDialog-actions span:nth-of-type(2) a' );
	}

	get submitButton() {
		return $( '#wpSave' );
	}

	get previewButton() {
		return $( '#wpPreview' );
	}

	get previewView() {
		return $( '#wikiPreview' );
	}

	get previewText() {
		return $( '#wikiPreview .mw-parser-output' );
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
	 * Disables VisualEditor, edit warning popups and sets test
	 * defaults to makes sure the feature is used and the help
	 * dialog hidden.
	 *
	 * @return {Promise} Promise from the mw.Api request
	 */
	async prepareUserSettings() {
		await Util.waitForModuleState( 'mediawiki.base' );
		return await browser.execute( async () => {
			await mw.loader.using( 'mediawiki.api' );
			return new mw.Api().saveOptions( {
				'visualeditor-hidebetawelcome': '1',
				'visualeditor-betatempdisable': '1',
				useeditwarning: '0',
				'twocolconflict-enabled': '1',
				twocolconflict: '1',
				'userjs-twocolconflict-hide-help-dialogue': '1'
			} );
		} );
	}

	/**
	 * @param {boolean} [show] Defaults to true.
	 * @return {Promise} Promise from the mw.Api request
	 */
	async toggleHelpDialog( show ) {
		const hide = show === false;
		await Util.waitForModuleState( 'mediawiki.base' );
		return await browser.execute( async ( setHide ) => {
			await mw.loader.using( 'mediawiki.api' );
			return new mw.Api().saveOption(
				'userjs-twocolconflict-hide-help-dialogue',
				setHide ? '1' : '0'
			);
		}, hide );
	}

	async prepareEditConflict() {
		await UserLoginPage.loginAdmin();
		await this.prepareUserSettings();
	}

	async showSimpleConflict() {
		await this.createConflict(
			// Includes HTML characters to check for proper escaping throughout the process.
			// Note the final assertions will look for "Line 1", "Change A" and such only, without
			// any of the HTML code being visible.
			'Line<span>1</span>\n\nLine2',
			'Line<span>1</span>\n\nChange <span lang="de">A</span>',
			'Line<span>1</span>\n\nChange <span lang="en">B</span>'
		);
		await this.waitForJS();
	}

	async apiEditPage( bot, title, text ) {
		await browser.call( async () => await bot.edit( title, text ) );
		await browser.pause( 500 );
	}

	async createConflict(
		startText,
		otherText,
		yourText,
		title = null,
		section = null
	) {
		title = ( title !== null ) ? title : ( Util.getTestString( 'conflict-title-' ) );

		// set initial page content
		await this.apiEditPage( await TestAccounts.adminBot, title, startText );

		// open editor and change the initial content
		if ( section !== null ) {
			await EditPage.openSectionForEditing( title, section );
		} else {
			await EditPage.openForEditing( title );
		}
		await EditPage.content.waitForExist();
		await EditPage.content.setValue( yourText );

		// conflicting edit by another party in the background
		await this.apiEditPage( await TestAccounts.otherBot(), title, otherText );

		// should trigger the edit conflict UI
		await EditPage.save.click();
	}

	async waitForJS() {
		await Util.waitForModuleState( 'ext.TwoColConflict.SplitJs' );
	}

	async testNoJs() {
		await browser.setCookies( {
			name: 'mw-twocolconflict-test-nojs',
			value: '1'
		} );
	}
}

module.exports = new EditConflictPage();
