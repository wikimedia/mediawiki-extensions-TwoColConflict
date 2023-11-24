'use strict';

const Page = require( 'wdio-mediawiki/Page' ),
	Util = require( 'wdio-mediawiki/Util' );

class PreferencesPage extends Page {
	get betaPreferencesLink() {
		return $( '//span[text() = "(prefs-betafeatures)"]' );
	}

	get twoColBetaLabel() {
		return $( '//*[@name="wptwocolconflict"]//parent::span' );
	}

	async openBetaFeaturesPreferences() {
		await super.openTitle( 'Special:Preferences', { uselang: 'qqx' } );
		await this.betaPreferencesLink.waitForDisplayed();
		await this.betaPreferencesLink.click();
	}

	async shouldUseTwoColConflict( shouldUse ) {
		await Util.waitForModuleState( 'mediawiki.base' );
		return await browser.execute( async ( use ) => {
			await mw.loader.using( 'mediawiki.api' );
			return new mw.Api().saveOption(
				'twocolconflict-enabled',
				use ? '1' : '0'
			);
		}, shouldUse );
	}

	async resetCoreHintVisibility() {
		await Util.waitForModuleState( 'mediawiki.base' );

		return await browser.execute( async () => {
			await mw.loader.using( 'mediawiki.api' );
			return new mw.Api().saveOption( 'userjs-twocolconflict-hide-core-hint', null );
		} );
	}

	async hasBetaFeatureSetting() {
		try {
			await this.twoColBetaLabel.waitForDisplayed();
			return true;
		} catch ( e ) {
			return false;
		}
	}
}

module.exports = new PreferencesPage();
