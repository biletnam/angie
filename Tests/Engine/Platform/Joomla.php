<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Engine\Platform;

use Akeeba\ANGIE\Tests\Engine\Driver;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Joomla extends Base
{
	/** @var array A list of Joomla! packages discovered: version => package filename */
	public $packages = [];

	/** @var array A list of extension packages to install: tmp subdir => package pathname */
	public $extensions = [];

	public function __construct()
	{
		global $angieTestConfig;

		$this->getPlatformPackages();

		$root       = $angieTestConfig['testplatforms']['joomla']['root'];
		$akeebaPath = $root.'/administrator/components/com_akeeba';

		$this->platform      = 'joomla';
		$this->versionPath   = $akeebaPath;
		$this->buildpath     = realpath($angieTestConfig['repositories']['akeeba'].'/build');
		$this->releasepath   = realpath($angieTestConfig['repositories']['akeeba'].'/release');
		$this->releaseprefix = 'pkg_akeeba-';
		$this->clipath       = $root.'/cli/akeeba-backup.php';
		$this->backuppath    = $akeebaPath.'/backup';
	}

	protected function getRepoVersionPath()
	{
		global $angieTestConfig;

		$repo = realpath($angieTestConfig['repositories']['akeeba']);

		$version = $repo.'/component/backend/version.php';

		return $version;
	}

	/**
	 * Populate $this->packages with the discovered Joomla! installation packages
	 */
	protected function getPlatformPackages()
	{
		$this->packages = [];

		$inboxDir = __DIR__ . '/../../inbox';

		$di = new \DirectoryIterator($inboxDir);

		/** @var \DirectoryIterator $file */
		foreach ($di as $file)
		{
			if ($file->isDir())
			{
				continue;
			}

			if ($file->getExtension() != 'zip')
			{
				continue;
			}

			$package = $file->getFilename();

			$parts = explode('_', $package);

			if (strpos($parts[0], 'Joomla') === false)
			{
				continue;
			}

			$version = $parts[1];
			$parts   = explode('-', $version);
			$version = $parts[0];

			$this->packages[$version] = $package;
		}
	}
	/**
	 * Create a new Joomla! site
	 *
	 * @throws \Exception
	 */
	public function createSite()
	{
		global $angieTestConfig;

		$package 	  = array_pop($this->packages);
		$joomlaConfig = $angieTestConfig['testplatforms']['joomla'];
		$siteRoot 	  = $joomlaConfig['root'];
		$testsRoot 	  = realpath(__DIR__ . '/../../');

		// Kill the target directory and all its subdirectories
		if (is_dir($siteRoot))
		{
			if (!$this->recursiveRemoveDirectory($siteRoot))
			{
				throw new \Exception("Cannot delete directory $siteRoot");
			}
		}

		if (!mkdir($siteRoot, 0755, true))
		{
			throw new \Exception("Cannot create directory $siteRoot");
		}

		// Extract the Joomla! ZIP archive
		$zip = new \ZipArchive();
		$zip->open($testsRoot . '/inbox/' . $package);

		if (!$zip->extractTo($siteRoot))
		{
			throw new \Exception("Cannot extract $package");
		}

		$zip->close();

		unset($zip);

		// Create a default .htaccess file
		@copy($siteRoot . '/htaccess.txt', $siteRoot . '/.htaccess');

		// Get a database driver
		$db = new Driver([
			'host'     => $joomlaConfig['db']['host'],
			'user'     => $joomlaConfig['db']['user'],
			'password' => $joomlaConfig['db']['pass'],
			'prefix'   => 'test_',
			'database' => 'joomlaintegration',
		]);

		// Create the database
		$this->populateDatabase($db, $testsRoot . '/_data/assets/joomla/new_db.sql');

		$db->select('joomlaintegration');

		// Install Joomla! core tables
		$this->populateDatabase($db, $siteRoot . '/installation/sql/mysql/joomla.sql');

		// Install sample data
		$this->populateDatabase($db, $siteRoot . '/installation/sql/mysql/sample_data.sql');

		// Install custom SQL
		$this->populateDatabase($db, $testsRoot . '/_data/assets/joomla/new_user_j3.sql');

		// Create custom configuration.php
		$siteUrlLive = rtrim($joomlaConfig['url'], '/');

		// Joomla! + live_site on Windows doesn't work due to a Joomla! bug regarding DIRECTORY_SEPARATOR.
		if (substr(PHP_OS, 0, 3) == 'WIN')
		{
			$siteUrlLive = '';
		}

		$dbHost      = isset($joomlaConfig['db']['host']) ? $joomlaConfig['db']['host'] : 'localhost';
		$replaceVars = [
			'SITEROOT'    => $siteRoot,
			'LIVESITEURL' => $siteUrlLive,
			'DBHOST'      => $dbHost,
		];

		$configText  = file_get_contents($testsRoot . '/_data/assets/joomla/configuration.php');

		foreach ($replaceVars as $k => $v)
		{
			$configText = str_replace('##' . $k . '##', $v, $configText);
		}

		file_put_contents($siteRoot . '/configuration.php', $configText);

		// Disable the Debug module
		$db->setQuery('UPDATE TABLE `test_extensions` SET `enabled`=0 WHERE `element` = \'debug\'');
		$db->query();

		// Delete the installation directory
		if (!$this->recursiveRemoveDirectory($siteRoot . '/installation'))
		{
			throw new \Exception("Cannot delete directory {$siteRoot}/installation");
		}
	}

	public function login(RemoteWebDriver &$webDriver)
	{
		global $angieTestConfig;

		// Get the token
		$url   = $angieTestConfig['site']['url'] . '/administrator/index.php';

		$webDriver->get($url);

		$usernameField = $webDriver->findElement(WebDriverBy::id('mod-login-username'));
		$usernameField->sendKeys('admin');

		usleep(200000);

		$passwordField = $webDriver->findElement(WebDriverBy::id('mod-login-password'));
		$passwordField->sendKeys('test');

		usleep(200000);

		// Click the login button
		$webDriver->findElement(WebDriverBy::className('login-button'))
				  ->click();

		// Wait until we are logged in
		$webDriver->wait(10, 150)->until(
			WebDriverExpectedCondition::titleContains('Control Panel')
		);
	}

	public function installExtension(RemoteWebDriver &$webDriver, $zipPath)
	{
		global $angieTestConfig;

		$siteUrl  = $angieTestConfig['site']['url'];
		$siteRoot = $angieTestConfig['site']['root'];
		$tmpPath  = $siteRoot . '/tmp/akeeba';

		// Make the temp directory
		if (@is_dir($tmpPath))
		{
			$this->recursiveRemoveDirectory($tmpPath);
		}

		mkdir($tmpPath, 0755, true);

		// Extract the extension ZIP file
		$zip = new \ZipArchive();
		$zip->open($zipPath);
		$zip->extractTo($tmpPath);
		$zip->close();
		unset($zip);

		$this->login($webDriver);

		$webDriver->get($siteUrl.'administrator/index.php?option=com_installer');
		// We do not have the "Install from Web" tab, so the "Install from folder" tab is the second one
		$webDriver->findElement(WebDriverBy::xpath('//*[@id="myTabTabs"]/li[2]/a'))->click();
		$webDriver->findElement(WebDriverBy::id('install_directory'))->clear()->sendKeys($tmpPath);

		$webDriver->findElement(WebDriverBy::id('installbutton_directory'))->click();

		$webDriver->wait(10)->until(
			WebDriverExpectedCondition::elementTextContains(
				WebDriverBy::id('system-message-container'), 'Installation of the package was successful')
		);

		// Delete the temporary directory
		if (@is_dir($tmpPath))
		{
			$this->recursiveRemoveDirectory($tmpPath);
		}

		// When we're done, let's visit the Control Page, so Akeeba Backup can setup some special properties
		$webDriver->get($siteUrl.'administrator/index.php?option=com_akeeba');
	}
}
