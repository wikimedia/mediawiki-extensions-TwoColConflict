const Page = require( 'wdio-mediawiki/Page' );

class BetaPreferencesPage extends Page {
	get betaPreferencesLink() { return browser.element( '#preferences .oo-ui-tabOptionWidget:nth-of-type(6)' ); }
	get twoColCheckbox() { return browser.element( 'input[name=wptwocolconflict]' ); }
	get twoColLabel() { return browser.element( '//*[@name="wptwocolconflict"]//parent::span' ); }
	get submit() { return browser.element( '#prefcontrol button' ); }

	open() {
		super.openTitle( 'Special:Preferences' );
		this.betaPreferencesLink.click();
	}

	enableTwoColConflictBetaFeature() {
		this.open();
		if ( !this.twoColCheckbox.getAttribute( 'checked' ) ) {
			this.twoColLabel.click();
			this.submit.click();
		}
	}
}

module.exports = new BetaPreferencesPage();
