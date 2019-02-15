<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Engine\Platform;


use Akeeba\ANGIE\Tests\Engine\Driver;

abstract class Base
{
	/** @var string	Path to the version.php file on test site */
	protected $versionPath;

	/** @var string	Software that should be installed to run the instagration (ABWP, Solo or AB Joomla) */
	protected $software;

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
	 * Returns the path to the version.php file inside the repository accordingly to the platform we need to backup
	 *
	 * @return string
	 */
	abstract protected function getRepoVersionPath();

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

	protected function assertConfigured()
	{
		if (!$this->versionPath)
		{
			throw new \RuntimeException('Missing path to Akeeba Backup version.php file');
		}
	}
}