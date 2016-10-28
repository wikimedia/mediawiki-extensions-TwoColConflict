<?php
/**
 * Hooks for TwoColConflict extension
 *
 * @file
 * @ingroup Extensions
 * @license GPL-2.0+
 */

class TwoColConflictHooks {
	public static function onAlternateEdit( EditPage $editPage ) {
		global $wgHooks;

		$key = array_search( 'TwoColConflictHooks::onAlternateEdit', $wgHooks );
		unset( $wgHooks[ 'AlternateEdit' ][ $key ] );

		$twoColConflictPage = new TwoColConflictPage( $editPage->mArticle );
		$twoColConflictPage->edit();

		return false;
	}
}
