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

	// Database information for testing the installation. !!! ITS CONTENTS WILL BE REMOVED !!!
	'test_restore_db' => [
		'driver' => 'Mysqli',
		'host'   => 'localhost',
		'user'   => 'nuked',
		'pass'   => 'nuked',
		'name'   => 'nuked',
		'prefix' => 'test_',
	],

	/**
	 * Main ANGIE for Joomla aite configuration.
	 *
	 **/
	'joomla'         => [
		// Absolute filesystem path to the site's root
		'root'            => '/var/www/guineapig',
		// Absolute URL to the site's frontend
		'url'             => 'http://localhost/guineapig/',
	],

	/**
	 * Main WordPress test site configuration
	 *
	 */
	'wordpress'    => [
		// Absolute filesystem path to the site's root
		'root'   => '/var/www/wpintegration',
		// Absolute URL to the site's frontend
		'url'    => 'http://wpintegration.local.web/',
	],
];
