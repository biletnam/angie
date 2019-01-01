<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

// Tell the installer to allow detection of utf8mb4 support by default
define('ANGIE_ALLOW_UTF8MB4_DEFAULT', true);
define('ANGIE_INSTALLER_NAME', 'Wordpress');

// Import Akeeba Replace's autoloader
require_once 'lib/Autoloader/Autoloader.php';