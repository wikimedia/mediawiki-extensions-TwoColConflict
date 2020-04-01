<?php

namespace TwoColConflict;

use MediaWiki\MediaWikiServices;

return [

	'TwoColConflictThreeWayMerge' => function ( MediaWikiServices $services ) {
		return new ThreeWayMerge();
	},

];
