<?php

namespace TwoColConflict;

use MediaWiki\MediaWikiServices;
use TwoColConflict\Logging\ThreeWayMerge;

return [

	'TwoColConflictThreeWayMerge' => function ( MediaWikiServices $services ) {
		return new ThreeWayMerge();
	},

];
