'use strict';

const Page = require( 'wdio-mediawiki/Page' ),
	Util = require( 'wdio-mediawiki/Util' );

class PreferencesPage extends Page {
	get twoColBetaLink() { return $( '#pt-betafeatures a' ); }
	get twoColBetaCheckbox() { return $( 'input[name=wptwocolconflict]' ); }
	get twoColBetaLabel() { return $( '//*[@name="wptwocolconflict"]//parent::span' ); }
	get saveBar() { return $( '.mw-prefs-buttons' ); }
	get submit() { return $( '#prefcontrol button' ); }

	openPreferences() {
		super.openTitle( 'Special:Preferences' );
	}

	openBetaPreferences() {
		this.openPreferences();
		this.twoColBetaLink.waitForDisplayed();
		this.twoColBetaLink.click();
	}

	disableEditWarning() {
		Util.waitForModuleState( 'mediawiki.base' );

		return browser.execute( function () {
			return mw.loader.using( 'mediawiki.api' ).then( function () {
				return new mw.Api().saveOption( 'useeditwarning', '0' );
			} );
		} );
	}

	shouldUseTwoColConflict( shouldUse ) {
		Util.waitForModuleState( 'mediawiki.base' );

		return browser.execute( function ( use ) {
			return mw.loader.using( 'mediawiki.api' ).then( function () {
				return new mw.Api().saveOption(
					'twocolconflict-enabled',
					use ? '1' : '0'
				);
			} );
		}, shouldUse );
	}

	shouldUseTwoColConflictBetaFeature( shouldUse ) {
		this.openBetaPreferences();
		if ( !this.hasBetaFeatureSetting() ) {
			return;
		}
		if ( !this.twoColBetaCheckbox.getAttribute( 'checked' ) === shouldUse ) {
			this.clickCheckBoxAndSave( this.twoColBetaCheckbox );
		}
	}

	resetCoreHintVisibility() {
		Util.waitForModuleState( 'mediawiki.base' );

		return browser.execute( function () {
			return mw.loader.using( 'mediawiki.api' ).then( function () {
				return new mw.Api().saveOption( 'userjs-twocolconflict-hide-core-hint', null );
			} );
		} );
	}

	clickCheckBoxAndSave( checkBox ) {
		// This workaround is needed when the preferences save bar
		// might obscure the feature's label and checkbox.
		browser.execute(
			( innerCheckBox, saveBar ) => {
				saveBar.style.visibility = 'hidden';
				innerCheckBox.click();
				saveBar.style.visibility = '';
			},
			checkBox,
			this.saveBar
		);
		this.submit.click();
	}

	hasBetaFeatureSetting() {
		try {
			this.twoColBetaLabel.waitForDisplayed( { timeout: 2000 } );
			return true;
		} catch ( e ) {
			return false;
		}
	}

	hasOptOutUserSetting() {
		Util.waitForModuleState( 'mediawiki.base' );
		return browser.execute( function () {
			let result;
			mw.loader.using( 'mediawiki.api' ).then( function () {
				result = new mw.Api().getOption( 'twocolconflict-enabled' );
			} );
			return result;
		} );

	}
}

module.exports = new PreferencesPage();
