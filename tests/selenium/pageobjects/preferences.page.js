'use strict';

const Page = require( 'wdio-mediawiki/Page' ),
	Util = require( 'wdio-mediawiki/Util' );

class PreferencesPage extends Page {
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
