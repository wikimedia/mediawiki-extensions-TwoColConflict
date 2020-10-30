'use strict';

const Page = require( 'wdio-mediawiki/Page' ),
	Util = require( 'wdio-mediawiki/Util' );

class PreferencesPage extends Page {
	get betaPreferencesLink() { return $( '#pt-betafeatures a' ); }
	get twoColBetaLabel() { return $( '//*[@name="wptwocolconflict"]//parent::span' ); }

	openPreferences() {
		super.openTitle( 'Special:Preferences' );
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
		Util.waitForModuleState( 'mediawiki.base' );
		return browser.execute( function ( use ) {
			return mw.loader.using( 'mediawiki.api' ).then( function () {
				return new mw.Api().saveOption(
					'twocolconflict',
					use ? '1' : '0'
				);
			} );
		}, shouldUse );
	}

	resetCoreHintVisibility() {
		Util.waitForModuleState( 'mediawiki.base' );

		return browser.execute( function () {
			return mw.loader.using( 'mediawiki.api' ).then( function () {
				return new mw.Api().saveOption( 'userjs-twocolconflict-hide-core-hint', null );
			} );
		} );
	}

	hasBetaFeatureSetting() {
		this.openPreferences();
		this.betaPreferencesLink.waitForDisplayed();
		this.betaPreferencesLink.click();
		try {
			this.twoColBetaLabel.waitForDisplayed( { timeout: 2000 } );
			return true;
		} catch ( e ) {
			return false;
		}
	}
}

module.exports = new PreferencesPage();
