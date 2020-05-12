const Page = require( 'wdio-mediawiki/Page' ),
	Util = require( 'wdio-mediawiki/Util' );

class PreferencesPage extends Page {
	get twoColBetaCheckbox() { return $( 'input[name=wptwocolconflict]' ); }
	get twoColBetaLabel() { return $( '//*[@name="wptwocolconflict"]//parent::span' ); }
	get editWarnCheckbox() { return $( 'input[name=wpuseeditwarning]' ); }
	get editWarnLabel() { return $( '#mw-input-wpuseeditwarning' ); }
	get twoColCheckbox() { return $( 'input[name=wptwocolconflict-enabled]' ); }
	get twoColLabel() { return $( '#mw-input-wptwocolconflict-enabled' ); }
	get saveBar() { return $( '.mw-prefs-buttons' ); }
	get submit() { return $( '#prefcontrol button' ); }

	openBetaPreferences() {
		super.openTitle( 'Special:Preferences', {}, 'mw-prefsection-betafeatures' );
	}

	openEditPreferences() {
		super.openTitle( 'Special:Preferences', {}, 'mw-prefsection-editing' );
	}

	disableEditWarning() {
		this.openEditPreferences();
		this.editWarnLabel.waitForDisplayed();
		if ( this.editWarnCheckbox.getAttribute( 'checked' ) ) {
			this.clickCheckBoxAndSave( this.editWarnCheckbox );
		}
	}

	shouldUseTwoColConflict( shouldUse ) {
		this.openEditPreferences();
		if ( !this.hasOptOutUserSetting() ) {
			return;
		}
		if ( !this.twoColCheckbox.getAttribute( 'checked' ) === shouldUse ) {
			this.clickCheckBoxAndSave( this.twoColCheckbox );
		}
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
			( checkBox, saveBar ) => {
				saveBar.style.visibility = 'hidden';
				checkBox.click();
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
		try {
			this.twoColLabel.waitForDisplayed( { timeout: 2000 } );
			return true;
		} catch ( e ) {
			return false;
		}
	}
}

module.exports = new PreferencesPage();
