const Page = require( 'wdio-mediawiki/Page' );

class BetaPreferencesPage extends Page {
	get twoColCheckbox() { return browser.element( 'input[name=wptwocolconflict]' ); }
	get twoColLabel() { return browser.element( '//*[@name="wptwocolconflict"]//parent::span' ); }
	get submit() { return browser.element( '#prefcontrol button' ); }

	open() {
		super.openTitle( 'Special:Preferences', {}, 'mw-prefsection-betafeatures' );
	}

	enableTwoColConflictBetaFeature() {
		this.open();
		try {
			// don't fail hard when not used as beta feature
			this.twoColLabel.waitForVisible( 3000 );
		} catch ( e ) {
			return;
		}
		if ( !this.twoColCheckbox.getAttribute( 'checked' ) ) {
			this.twoColLabel.waitForVisible();
			const saveBar = browser.element( '.mw-prefs-buttons' );
			// This workaround is needed when the preferences save bar
			// might obscure the feature's label and checkbox.
			browser.execute(
				( twoColCheckbox, saveBar ) => {
					saveBar.style.visibility = 'hidden';
					twoColCheckbox.click();
					saveBar.style.visibility = '';
				},
				this.twoColCheckbox.value,
				saveBar.value
			);
			this.submit.click();
		}
	}
}

module.exports = new BetaPreferencesPage();
