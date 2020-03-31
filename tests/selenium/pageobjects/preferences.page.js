const Page = require( 'wdio-mediawiki/Page' );

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
		// This workaround is needed when the preferences save bar
		// might obscure the feature's label and checkbox.
		browser.execute(
			( checkBox, saveBar ) => {
				saveBar.style.visibility = 'hidden';
				checkBox.click();
				saveBar.style.visibility = '';
			},
			checkBox.value,
			this.saveBar.value
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
