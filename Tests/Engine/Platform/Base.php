<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Engine\Platform;

use Akeeba\ANGIE\Tests\Engine\Driver;
use Facebook\WebDriver\Remote\RemoteWebDriver;

abstract class Base
{
	/** @var string	Path to the version.php file on test site */
	protected $versionPath;

	protected $clipath;

	protected $backuppath;

	protected $releasepath;

	protected $releaseprefix;

	protected $buildpath;

	protected $buildaction = 'git';

	protected $platform;

	/**
	 * Checks if test site has Akeeba Backup installed and is updated to latest version
	 */
	public function akeebaNeedsInstall()
	{
		$this->assertConfigured();

		// Missing file, extension not installed for sure
		if (!file_exists($this->versionPath.'/version.php'))
		{
			return true;
		}

		// version.php mismatch? Must install the component.
		$source = $this->getRepoVersionPath();

		if (!is_file($source))
		{
			// Someone gave us a virgin repository?
			throw new \RuntimeException('You must run phing in the build directory before running the tests.');
		}

		if (md5_file($source) != md5_file($this->versionPath.'/version.php'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Searches and, if needed, builds the extension package required for the platform under test
	 *
	 * @return mixed
	 */
	public function getExtensionZip()
	{
		// The development release version (rev1234ABC where 1234ABC is the Git commit hash)
		$devVersion = 'rev' . $this->getGitCommitHash();

		// Find the installation package
		$package = $this->findInstallationPackage($devVersion);

		if (empty($package))
		{
			// If the package does not exist try to build it
			$this->buildPackage();
			$package = $this->findInstallationPackage($devVersion);
		}

		if (empty($package))
		{
			// Someone gave us a virgin repository?
			throw new \RuntimeException("Package not found. You must run phing git in the build directory before running the tests.");
		}

		return realpath($package);
	}

	/**
	 * Searches for platform archives that should be used to install the test site
	 *
	 * @return mixed
	 */
	abstract protected function getPlatformPackages();

	/**
	 * Performs the required steps to actuall install a test site
	 *
	 * @return void
	 */
	abstract public function createSite();

	/**
	 * Logins inside the administrative area of the test platform
	 *
	 * @param RemoteWebDriver $webDriver
	 *
	 * @return mixed
	 */
	abstract public function login(RemoteWebDriver &$webDriver);

	/**
	 * Installs the package in the test platform
	 *
	 * @param RemoteWebDriver $webDriver
	 * @param                 $zipPath
	 *
	 * @return mixed
	 */
	abstract public function installExtension(RemoteWebDriver &$webDriver, $zipPath);

	/**
	 * Returns the path to the version.php file inside the repository accordingly to the platform we need to backup
	 *
	 * @return string
	 */
	abstract protected function getRepoVersionPath();

	public function takeCliBackup()
	{
		global $angieTestConfig;

		$this->assertConfigured();

		$commandLine = $angieTestConfig['php']['cli'] . ' ' . $this->clipath;

		$output = [];
		exec($commandLine, $output);

		$output = implode("\n", $output);

		$files = glob($this->backuppath.'/*.jpa');

		if (!$files)
		{
			throw new \RuntimeException("Could not find a backup archive. ");
		}

		$archive = $files[0];

		$result = rename($archive, __DIR__.'/../../_data/archives/'.strtolower($this->platform).'.jpa');

		if (!$result)
		{
			throw new \RuntimeException('An error occurred while copying backup archives on Test folder');
		}
	}

	/**
	 * Find the appropriate installation package file
	 *
	 * @param   string  $version   Set to null to find any version. Otherwise specify the version you want found.
	 *
	 * @return  string|null  Path to the package file or null if it's not found
	 */
	public function findInstallationPackage($version = null)
	{
		$this->assertConfigured();

		$path   = $this->releasepath;
		$prefix = $this->releaseprefix;
		$suffix = getenv('AKEEBA_TESTS_USECORE') ? '-core' : '-pro';
		$ret    = null;

		// The release directory is not present
		if (!is_dir($path))
		{
			return null;
		}

		// Try to find the right package
		$di = new \DirectoryIterator($path);

		foreach ($di as $file)
		{
			if (!$file->isFile())
			{
				continue;
			}

			if ($file->getExtension() != 'zip')
			{
				continue;
			}

			$fileName = $file->getBasename('.zip');

			if (substr($fileName, 0, strlen($prefix)) != $prefix)
			{
				continue;
			}

			if (substr($fileName, -strlen($suffix)) != $suffix)
			{
				continue;
			}

			$pathname = $file->getPathname();

			if (!is_null($version))
			{
				$parts = explode ('-', $fileName);

				if ($version != $parts[1])
				{
					continue;
				}
			}

			$ret      = $pathname;

			break;
		}

		$di = null;

		return $ret;
	}

	public function getGitCommitHash($force = false)
	{
		static $version = null;

		$this->assertConfigured();

		if ($force || is_null($version))
		{
			$commandLine = 'git rev-parse --short HEAD';
			$output      = '';
			$cwd         = getcwd();
			$buildDir    = realpath($this->buildpath);
			chdir($buildDir);
			exec($commandLine, $output);
			chdir($cwd);

			$version = strtoupper(trim(implode('', $output)));
		}

		return $version;
	}

	/**
	 * Build the extension package
	 */
	public function buildPackage()
	{
		global $angieTestConfig;

		$this->assertConfigured();

		$action      = $this->buildaction;
		$commandLine = $angieTestConfig['php']['phing'] . " $action";
		$buildDir    = realpath($this->buildpath);

		$output = '';
		$cwd    = getcwd();
		chdir($buildDir);
		exec($commandLine, $output);
		chdir($cwd);
	}

	protected function assertConfigured()
	{
		if (!$this->versionPath)
		{
			throw new \RuntimeException('Missing path to Akeeba Backup version.php file');
		}

		if (!$this->clipath)
		{
			throw new \RuntimeException('Platform must specify the path for a CLI backup');
		}

		if (!$this->backuppath)
		{
			throw new \RuntimeException('Platform must specify the backup output');
		}

		if (!$this->platform)
		{
			throw new \RuntimeException('You must specify the platform of this class (ie Joomla, WordPress etc etc)');
		}

		if (!$this->buildpath)
		{
			throw new \RuntimeException('You must specify the buildpath for the software for this platform');
		}

		if (!$this->releasepath)
		{
			throw new \RuntimeException('You must specify the release path for the software for this platform');
		}

		if (!$this->releaseprefix)
		{
			throw new \RuntimeException('You must specify the release prefix (ie pkg_akeeba-) used by the software for this platform');
		}
	}
}