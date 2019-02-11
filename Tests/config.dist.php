<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/**
 * Configuration for integration tests using Selenium and PHPUnit
 */
$angieTestConfig = [

	/**
	 * Configuration of PHP executables
	 */
	'php'          => [
		// Path to the PHP CLI executable
		'cli'   => '/usr/bin/php',
		// Path to the Phing executable
		'phing' => '/usr/bin/phing',
	],

	// Holds the data required for the restoration process
	'angie' => [
		// Database information for testing the installation. !!! ITS CONTENTS WILL BE REMOVED !!!
		'test_restore_db' => [
			'driver' => 'Mysqli',
			'host'   => 'localhost',
			'user'   => 'nuked',
			'pass'   => 'nuked',
			'name'   => 'nuked'
		],

		// Absolute filesystem path to the site's root
		'root'            => '/var/www/html/angieintegration',
		// Absolute URL to the site's frontend
		'url'             => 'http://localhost/angieintegration/',
	],

	// These are the platforms that will backed up and ANGIE will try to restore automatically
	// Please note that as for Solo integration tests, those sites should be full installations and not symlinked to dev
	'testplatforms' => [
		'solo'		=> [
			'root'	=> '/var/www/html/solotestinstallation',
			'url'   => 'http://localhost/guineapig/',
		],

		'wordpress'	=> [
			'root'  => '/var/www/wpintegration',
			'url'   => 'http://wpintegration.local.web/',
		],

		'joomla'	=> [
			'root'  => '/var/www/joomla',
			'url'   => 'http://localhost/joomla/',
		],
	],

	/**
	 * Where you can find other code repositories we need to use to run certain tests
	 */
	'repositories' => [
		// Akeeba Kickstart - used to extract test data
		'kickstart' => __DIR__ . '/../../kickstart',
	],
];
