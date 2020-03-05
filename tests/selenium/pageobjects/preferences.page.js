const Page = require( 'wdio-mediawiki/Page' );

class PreferencesPage extends Page {
	get twoColBetaCheckbox() { return browser.element( 'input[name=wptwocolconflict]' ); }
	get twoColBetaLabel() { return browser.element( '//*[@name="wptwocolconflict"]//parent::span' ); }
	get editWarnCheckbox() { return browser.element( 'input[name=wpuseeditwarning]' ); }
	get editWarnLabel() { return browser.element( '#mw-input-wpuseeditwarning' ); }
	get twoColCheckbox() { return browser.element( 'input[name=wptwocolconflict-enabled]' ); }
	get twoColLabel() { return browser.element( '#mw-input-wptwocolconflict-enabled' ); }
	get submit() { return browser.element( '#prefcontrol button' ); }

	openBetaPreferences() {
		super.openTitle( 'Special:Preferences', {}, 'mw-prefsection-betafeatures' );
	}

	openEditPreferences() {
		super.openTitle( 'Special:Preferences', {}, 'mw-prefsection-editing' );
	}

	disableEditWarning() {
		this.openEditPreferences();
		this.editWarnLabel.waitForVisible();
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

	enableTwoColConflictBetaFeature() {
		this.openBetaPreferences();
		if ( !this.hasBetaFeatureSetting() ) {
			return;
		}
		this.hasBetaFeatureSetting();
		if ( !this.twoColBetaCheckbox.getAttribute( 'checked' ) ) {
			this.clickCheckBoxAndSave( this.twoColBetaCheckbox );
		}
	}

	clickCheckBoxAndSave( checkBox ) {
		const saveBar = browser.element( '.mw-prefs-buttons' );
		// This workaround is needed when the preferences save bar
		// might obscure the feature's label and checkbox.
		browser.execute(
			( checkBox, saveBar ) => {
				saveBar.style.visibility = 'hidden';
				checkBox.click();
				saveBar.style.visibility = '';
			},
			checkBox.value,
			saveBar.value
		);
		this.submit.click();
	}

	hasBetaFeatureSetting() {
		try {
			this.twoColBetaLabel.waitForVisible( 2000 );
			return true;
		} catch ( e ) {
			return false;
		}
	}

	hasOptOutUserSetting() {
		try {
			this.twoColLabel.waitForVisible( 2000 );
			return true;
		} catch ( e ) {
			return false;
		}
	}
}

module.exports = new PreferencesPage();
