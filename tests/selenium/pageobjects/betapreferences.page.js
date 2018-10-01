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
		this.twoColLabel.waitForVisible();
		if ( !this.twoColCheckbox.getAttribute( 'checked' ) ) {
			this.twoColLabel.click();
			this.submit.click();
		}
	}
}

module.exports = new BetaPreferencesPage();
