<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Engine;

class Joomla
{

	/** @var array A list of Joomla! packages discovered: version => package filename */
	public $packages = [];

	/** @var array A list of extension packages to install: tmp subdir => package pathname */
	public $extensions = [];

	function __construct()
	{
		$this->getJoomlaPackages();
		//$this->getExtensionPackages();
	}

	/**
	 * Populate $this->packages with the discovered Joomla! installation packages
	 */
	protected function getJoomlaPackages()
	{
		$this->packages = [];

		$inboxDir = __DIR__ . '/../inbox';

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

	protected function getExtensionPackages()
	{
		$this->extensions = [];

		// Add Admin Tools Professional package
		$di = new \DirectoryIterator(__DIR__ . '/../../../release');
		/** @var \DirectoryIterator $file */
		foreach ($di as $file)
		{
			if (!$file->isFile())
			{
				continue;
			}

			$package = $file->getFilename();

			if (strpos($package, 'pkg_admintools') === false)
			{
				continue;
			}

			if (strpos($package, '-pro') !== false)
			{
				$this->extensions['atpro'] = $file->getRealPath();
			}

			if (strpos($package, '-core') !== false)
			{
				$this->extensions['atcore'] = $file->getRealPath();
			}

			continue;
		}

		// Add other discovered extensions
		$inboxDir = __DIR__ . '/../inbox';

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

			if (strpos($parts[0], 'Joomla') !== false)
			{
				continue;
			}

			$name                    = 'ext' . md5(microtime(false));
			$this->extensions[$name] = $file->getRealPath();
		}
	}

	/**
	 * Create a new Joomla! site
	 *
	 * @param string $package
	 *
	 * @throws \Exception
	 */
	public function createJoomlaSite($package)
	{
		global $angieTestConfig;

		$joomlaConfig = $angieTestConfig['testplatforms']['joomla'];

		$siteRoot = $joomlaConfig['root'];

		$testsRoot = realpath(__DIR__ . '/../');

		/**/
		// Kill the target directory and all its subdirectories
		echo "    Creating site root\n";
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
		echo "    Extracting Joomla!\n";
		$zip = new \ZipArchive();
		$zip->open($testsRoot . '/inbox/' . $package);

		if (!$zip->extractTo($siteRoot))
		{
			throw new \Exception("Cannot extract $package");
		}

		$zip->close();

		unset($zip);

		// Create a default .htaccess file
		echo "    Creating .htaccess\n";
		@copy($siteRoot . '/htaccess.txt', $siteRoot . '/.htaccess');

		// Get a database driver
		$db = new Driver([
			'host'     => $joomlaConfig['db']['host'],
			'user'     => $joomlaConfig['db']['user'],
			'password' => $joomlaConfig['db']['pass'],
			'prefix'   => 'test_',
			'database' => 'integration',
		]);

		// Create the database
		echo "    Creating new database\n";
		$this->populateDatabase($db, $testsRoot . '/assets/new_db.sql');

		$db->select('integration');

		// Install Joomla! core tables
		echo "    Installing core Joomla! tables\n";
		$this->populateDatabase($db, $siteRoot . '/installation/sql/mysql/joomla.sql');

		// Install sample data (Joomla! 3.x only)
		if (file_exists($siteRoot . '/installation/sql/mysql/sample_data.sql'))
		{
			echo "    Installing sample data\n";
			$this->populateDatabase($db, $siteRoot . '/installation/sql/mysql/sample_data.sql');
		}

		// Install custom SQL
		$isJoomla4 = file_exists($siteRoot . '/templates/cassiopeia/index.php');
		// Install a different file when it's Joomla 4 because it does not support MD5 passwords
		$file = $isJoomla4 ? 'new_user_j4.sql' : 'new_user_j3.sql';
		echo "    Installing custom SQL\n";

		$this->populateDatabase($db, $testsRoot . '/assets/' . $file);

		// Create custom configuration.php
		echo "    Creating configuration.php\n";
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
		$configText  = file_get_contents($testsRoot . '/assets/configuration.php');
		foreach ($replaceVars as $k => $v)
		{
			$configText = str_replace('##' . $k . '##', $v, $configText);
		}
		file_put_contents($siteRoot . '/configuration.php', $configText);

		// Enable user registration
		echo "    Enabling user registration\n";
		$db->setQuery('UPDATE TABLE `test_extensions` SET `params` = \'{"allowUserRegistration":"1","new_usertype":"2","guest_usergroup":"9","sendpassword":"1","useractivation":"1","mail_to_admin":"0","captcha":"","frontend_userparams":"1","site_language":"0","change_login_name":"0","reset_count":"10","reset_time":"1","minimum_length":"4","minimum_integers":"0","minimum_symbols":"0","minimum_uppercase":"0","save_history":"1","history_limit":5,"mailSubjectPrefix":"","mailBodySuffix":""}\' WHERE `element` = \'com_users\'');
		$db->query();

		// Disable the Debug module
		echo "    Disable the debug module\n";
		$db->setQuery('UPDATE TABLE `test_extensions` SET `enabled`=0 WHERE `element` = \'debug\'');
		$db->query();

		// Delete the installation directory
		echo "    Removing the installation directory\n";
		if (!$this->recursiveRemoveDirectory($siteRoot . '/installation'))
		{
			throw new \Exception("Cannot delete directory {$siteRoot}/installation");
		}
	}

	/**
	 * Login a user to the back-end
	 *
	 * @param Surfer $surfer          The surfer class we will log in with (holds the cookie jar)
	 * @param array  $headers         Any HTTP headers you want to pass
	 * @param string $username        The username to log in with, default is 'admin'
	 * @param string $extraAdminQuery Extra URL query for the login page
	 *
	 * @return object
	 */
	public function login(Surfer &$surfer, array $headers = [], $username = 'admin', $extraAdminQuery = '')
	{
		global $angieTestConfig;

		// TODO Use the WebDriver instead of the custom Surfer

		/*// Get the token
		$url   = $testConfiguration['site']['url'] . '/administrator/index.php' . $extraAdminQuery;
		$token = $surfer->getLoginToken($url, $headers);

		// Log in
		$data = [
			'username' => $username,
			'passwd'   => 'test',
			'lang'     => '',
			'option'   => 'com_login',
			'task'     => 'login',
			'return'   => base64_encode('index.php'),
			$token     => 1,
		];

		return $surfer->postForm($url, $data, false, $headers);*/
	}

	public function installExtension(Surfer &$surfer, $zipPath, $tmpSubDir = 'atpro')
	{
		global $angieTestConfig;

		// TODO Use the WebDriver instead of the custom Surfer

		return;

		$siteRoot = $testConfiguration['site']['root'];
		$tmpPath  = $siteRoot . '/tmp/' . $tmpSubDir;

		// Make the temp directory
		if (@is_dir($tmpPath))
		{
			$this->recursiveRemoveDirectory($tmpPath);
		}
		mkdir($tmpPath, 0755, true);

		// If the ZIP path is a URL, download it to tmp
		if ((substr($zipPath, 0, 8) == 'https://') || substr($zipPath, 0, 7) == 'http://')
		{
			$zipData = @file_get_contents($zipPath);
			$zipPath = $siteRoot . '/tmp/extension.zip';
			file_put_contents($zipPath, $zipData);
			unset($zipData);
		}

		// Extract the extension ZIP file
		$zip = new \ZipArchive();
		$zip->open($zipPath);
		$zip->extractTo($tmpPath);
		$zip->close();
		unset($zip);

		// Get the token
		$url   = $testConfiguration['site']['url'] . '/administrator/index.php?option=com_installer';
		$token = $surfer->getInstallerToken($url);

		// Install from URL
		$url  .= '&view=install';
		$data = [
			'install_directory' => $testConfiguration['site']['root'] . '/tmp/' . $tmpSubDir,
			'type'              => '',
			'installtype'       => 'folder',
			'task'              => 'install.install',
			$token              => 1,
		];

		$ret = $surfer->postForm($url, $data);

		// Delete the temporary directory
		if (@is_dir($tmpPath))
		{
			$this->recursiveRemoveDirectory($tmpPath);
		}

		return $ret;
	}

	/**
	 * Recursively remove a directory and all its contents
	 *
	 * @param $directory
	 *
	 * @return bool
	 */
	public function recursiveRemoveDirectory($directory)
	{
		// Trim trailing slash
		$directory = rtrim($directory, DIRECTORY_SEPARATOR . '/');

		if (!file_exists($directory) || !is_dir($directory))
		{
			return false;
		}

		if (!is_readable($directory))
		{
			return false;
		}

		$di = new \DirectoryIterator($directory);

		/** @var \DirectoryIterator $item */
		foreach ($di as $item)
		{
			if ($di->isDot())
			{
				continue;
			}

			$filePath = $directory . DIRECTORY_SEPARATOR . $item->getFilename();

			if ($di->isDir())
			{
				$this->recursiveRemoveDirectory($filePath);
				continue;
			}

			if (!@unlink($filePath))
			{
				@chmod($filePath, 0777);
				@file_put_contents($filePath, '');
				@unlink($filePath);
			}
		}

		@rmdir($directory);

		return true;
	}

	public function populateDatabase(Driver $db, $schema)
	{
		$return = true;

		// Get the contents of the schema file.
		if (!($buffer = file_get_contents($schema)))
		{
			throw new \Exception("Cannot open $schema");
		}

		// Get an array of queries from the schema and process them.
		$queries = $this->_splitQueries($buffer);

		foreach ($queries as $query)
		{
			// Trim any whitespace.
			$query = trim($query);

			// If the query isn't empty and is not a MySQL or PostgreSQL comment, execute it.
			if (!empty($query) && ($query{0} != '#') && ($query{0} != '-'))
			{
				// Execute the query.
				$db->setQuery($query);

				$db->query();
			}
		}

		return $return;
	}

	/**
	 * Method to split up queries from a schema file into an array.
	 *
	 * @param   string $query SQL schema.
	 *
	 * @return  array  Queries to perform.
	 *
	 * @since   3.1
	 */
	protected function _splitQueries($query)
	{
		$buffer    = array();
		$queries   = array();
		$in_string = false;

		// Trim any whitespace.
		$query = trim($query);

		// Remove comment lines.
		$query = preg_replace("/\n\#[^\n]*/", '', "\n" . $query);

		// Remove PostgreSQL comment lines.
		$query = preg_replace("/\n\--[^\n]*/", '', "\n" . $query);

		// Find function
		$funct = explode('CREATE OR REPLACE FUNCTION', $query);

		// Save sql before function and parse it
		$query = $funct[0];

		// Parse the schema file to break up queries.
		for ($i = 0; $i < strlen($query) - 1; $i++)
		{
			if ($query[$i] == ";" && !$in_string)
			{
				$queries[] = substr($query, 0, $i);
				$query     = substr($query, $i + 1);
				$i         = 0;
			}

			if ($in_string && ($query[$i] == $in_string) && $buffer[1] != "\\")
			{
				$in_string = false;
			}
			elseif (!$in_string && ($query[$i] == '"' || $query[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\"))
			{
				$in_string = $query[$i];
			}
			if (isset ($buffer[1]))
			{
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $query[$i];
		}

		// If the is anything left over, add it to the queries.
		if (!empty($query))
		{
			$queries[] = $query;
		}

		// Add function part as is
		for ($f = 1; $f < count($funct); $f++)
		{
			$queries[] = 'CREATE OR REPLACE FUNCTION ' . $funct[$f];
		}

		return $queries;
	}
}
