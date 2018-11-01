<?php
$hardlink_files = [];

$symlink_files = [
	'translations/angie/en-GB.ini'      => 'angie/installation/angie/language/en-GB.ini',
	'translations/drupal7/en-GB.ini'    => 'angie/platforms/drupal7/language/en-GB.ini',
	'translations/drupal8/en-GB.ini'    => 'angie/platforms/drupal8/language/en-GB.ini',
	'translations/generic/en-GB.ini'    => 'angie/platforms/generic/language/en-GB.ini',
	'translations/grav/en-GB.ini'       => 'angie/platforms/grav/language/en-GB.ini',
	'translations/magento/en-GB.ini'    => 'angie/platforms/magento/language/en-GB.ini',
	'translations/magento2/en-GB.ini'   => 'angie/platforms/magento2/language/en-GB.ini',
	'translations/moodle/en-GB.ini'     => 'angie/platforms/moodle/language/en-GB.ini',
	'translations/octobercms/en-GB.ini' => 'angie/platforms/octobercms/language/en-GB.ini',
	'translations/pagekit/en-GB.ini'    => 'angie/platforms/pagekit/language/en-GB.ini',
	'translations/phpbb/en-GB.ini'      => 'angie/platforms/phpbb/language/en-GB.ini',
	'translations/prestashop/en-GB.ini' => 'angie/platforms/prestashop/language/en-GB.ini',
	'translations/wordpress/en-GB.ini'  => 'angie/platforms/wordpress/language/en-GB.ini',

	# FEF
	'../fef/js/dropdown.min.js'         => 'angie/installation/template/flat/js/dropdown.min.js',
	'../fef/js/menu.min.js'             => 'angie/installation/template/flat/js/menu.min.js',
	'../fef/js/tabs.min.js'             => 'angie/installation/template/flat/js/tabs.min.js',
	'../fef/sa-css/style.min.css'       => 'angie/installation/template/flat/css/fef.min.css',
];

$symlink_folders = [
	'../wpreplace/src/lib' => 'angie/platforms/wordpress/lib',
	'../fef/fonts/akeeba'  => 'angie/installation/template/flat/fonts/akeeba',
	'../fef/fonts/Ionicon' => 'angie/installation/template/flat/fonts/Ionicon',
];
