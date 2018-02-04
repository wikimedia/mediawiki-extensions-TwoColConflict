<?php

$cfg = require __DIR__ . '/../../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'./../../extensions/BetaFeatures',
		'./../../extensions/EventLogging',
		'./../../extensions/WikiEditor',
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'./../../extensions/BetaFeatures',
		'./../../extensions/EventLogging',
		'./../../extensions/WikiEditor',
	]
);

$cfg['suppress_issue_types'] = [
	// approximate error count: 1
	"PhanParamSignatureMismatch",
];

return $cfg;
