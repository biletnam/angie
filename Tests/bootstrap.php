<?php
/**
 * @package    solo
 * @copyright  Copyright (c)2014-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU GPL version 3 or later
 */

/**
 * IMPORTANT: You must ALWAYS set backupGlobals="false" to phpunit.xml
 *
 * The ABWP integration script creates the $akeebaBackupWordPressContainer global which contains the Pimple
 * container for the AWF application. The container implements every service as a Closure. PHPUnit will
 * try to serialize the globals before each test and restore them afterwards. However, closures cannot
 * be serialized. Since the Container includes closures, this leads to the unhelpful error message
 * “Exception : Serialization of 'Closure' is not allowed” with no debug trace. It will take forever to
 * figure out where this comes from so here I am documenting it.
 */

if (!file_exists(__DIR__ . '/../vendor/autoload.php'))
{
	die('You need to install Composer and run `composer install` before running the tests.');
}

ini_set('display_errors', 1);
error_reporting(E_ALL);


// Load the test configuration
global $angieTestConfig;
require_once 'config.php';

/**
 * Allow per OS configuration overrides (config_windows.php, config_linux.php and config_macos.php), useful if you want
 * a single repository to be used by multiple VMs for cross-platform acceptance testing.
 */
$os = 'other';

if (stristr(PHP_OS, 'windows') || stristr(PHP_OS, 'win32') || stristr(PHP_OS, 'winnt'))
{
	$os = 'windows';
}
elseif (stristr(PHP_OS, 'linux') || stristr(PHP_OS, 'cygwin') || stristr(PHP_OS, 'unix'))
{
	$os = 'linux';
}
elseif (stristr(PHP_OS, 'darwin'))
{
	$os = 'macos';
}

if (file_exists(__DIR__ . "/config_$os.php"))
{
	require_once __DIR__ . "/config_$os.php";
}

/**
 * The rest of the bootstrap process is handled by the listener
 */
