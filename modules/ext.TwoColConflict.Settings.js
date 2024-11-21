/**
 * @constructor
 */
const Settings = function () {};

Object.assign( Settings.prototype, {
	/**
	 * @param {string} name
	 * @return {boolean}
	 */
	loadBoolean: function ( name ) {
		if ( mw.user.isNamed() ) {
			return mw.user.options.get( 'userjs-twocolconflict-' + name ) === '1';
		} else {
			return ( mw.storage.get( 'mw-twocolconflict-' + name ) ||
				mw.cookie.get( '-twocolconflict-' + name ) ) === '1';
		}
	},

	/**
	 * @param {string} name
	 * @param {boolean} value
	 */
	saveBoolean: function ( name, value ) {
		value = value ? '1' : '0';
		if ( mw.user.isNamed() ) {
			( new mw.Api() ).saveOption( 'userjs-twocolconflict-' + name, value );
		} else {
			if ( !mw.storage.set( 'mw-twocolconflict-' + name, value ) ) {
				mw.cookie.set( '-twocolconflict-' + name, value ); // use cookie when localStorage is not available
			}
		}
	}
} );

module.exports = Settings;
