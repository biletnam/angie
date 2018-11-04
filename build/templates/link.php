<?php
$hardlink_files = [];

$symlink_files = [
	# Translations
	'translations/angie/en-GB.ini'      => 'angie/installation/angie/language/en-GB.ini',
	'translations/generic/en-GB.ini'    => 'angie/platforms/generic/language/en-GB.ini',
	'translations/prestashop/en-GB.ini' => 'angie/platforms/prestashop/language/en-GB.ini',
	'translations/wordpress/en-GB.ini'  => 'angie/platforms/wordpress/language/en-GB.ini',

	# FEF JavaScript
	'../fef/js/dropdown.min.js'         => 'angie/installation/template/flat/js/dropdown.min.js',
	'../fef/js/menu.min.js'             => 'angie/installation/template/flat/js/menu.min.js',
	'../fef/js/tabs.min.js'             => 'angie/installation/template/flat/js/tabs.min.js',

	# FEF CSS
	'../fef/sa-css/style.min.css'       => 'angie/installation/template/flat/css/fef.min.css',
];

$symlink_folders = [
	// Akeeba Replace library
	'../wpreplace/src/lib' => 'angie/platforms/wordpress/lib',

	// FEF fonts
	'../fef/fonts/akeeba'  => 'angie/installation/template/flat/fonts/akeeba',
	'../fef/fonts/Ionicon' => 'angie/installation/template/flat/fonts/Ionicon',
];
