<?php
/**
 * TwoColConflict MediaWiki Extension
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'TwoColConflict' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['TwoColConflict'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for TwoColConflict extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the TwoColConflict extension requires MediaWiki 1.25+' );
}
