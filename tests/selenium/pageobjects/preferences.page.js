const Page = require( 'wdio-mediawiki/Page' );

class PreferencesPage extends Page {
	get twoColCheckbox() { return browser.element( 'input[name=wptwocolconflict]' ); }
	get twoColLabel() { return browser.element( '//*[@name="wptwocolconflict"]//parent::span' ); }
	get editWarnCheckbox() { return browser.element( 'input[name=wpuseeditwarning]' ); }
	get editWarnLabel() { return browser.element( '#mw-input-wpuseeditwarning' ); }
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

	enableTwoColConflictBetaFeature() {
		this.openBetaPreferences();
		try {
			// don't fail hard when not used as beta feature
			this.twoColLabel.waitForVisible( 3000 );
		} catch ( e ) {
			return;
		}
		if ( !this.twoColCheckbox.getAttribute( 'checked' ) ) {
			this.clickCheckBoxAndSave( this.twoColCheckbox );
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
}

module.exports = new PreferencesPage();
