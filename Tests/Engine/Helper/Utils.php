<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Engine\Helper;

use Akeeba\ANGIE\Tests\Engine\Driver;
use DirectoryIterator;

abstract class Utils
{
	/**
	 * Recycle the folder containing the screenshots
	 *
	 * @return  void
	 */
	public static function recycleScreenshotsFolder()
	{
		// Recycle the screenshots directory
		$screenshotsPath = __DIR__ . '/../screenshots';
		$di              = new DirectoryIterator($screenshotsPath);

		foreach ($di as $file)
		{
			if ($file->isDot())
			{
				continue;
			}

			if ($file->isDir())
			{
				self::recursiveRemoveDirectory($file->getPathname());
			}
		}
	}

	/**
	 * Deletes ANGIE session data stored inside the tmp directory
	 */
	public static function deleteAngieSessionData()
	{
		global $angieTestConfig;

		// Temporary directory holding the session data is not symlinked
		$folder = $angieTestConfig['angie']['root'].'/installation/tmp';
		$di     = new DirectoryIterator($folder);

		foreach ($di as $file)
		{
			// We're interested only in real files
			if ($file->isDot() || $file->isDir() || $file->isLink())
			{
				continue;
			}

			// Remove only ANGIE session files
			if (strpos($file->getFilename(), 'storagedata') === 0)
			{
				unlink($file->getPathname());
			}
		}
	}

	public static function extractArchive($archive, $targetDir)
	{
		global $angieTestConfig;

		$kickstart = $angieTestConfig['repositories']['kickstart'].'/output/kickstart.php';
		$kickstart = realpath($kickstart);

		if (!file_exists($kickstart))
		{
			throw new \RuntimeException("Couldn't find Kickstart compiled package. Please run 'phing git' in Kickstart repository");
		}

		$cmd  = $angieTestConfig['php']['cli'].' ';
		$cmd .= escapeshellarg($kickstart).' ';
		$cmd .= escapeshellarg($archive).' ';
		$cmd .= escapeshellarg($targetDir);

		$exit_code = null;
		$output = [];

		// Let's use Kickstart CLI interface to extract the archive
		exec($cmd, $output, $exit_code);

		if ($exit_code !== 0)
		{
			throw new \RuntimeException('Something went wrong during extraction process');
		}
	}

	/**
	 * Calls ANGIE script to symlink the correct platform inside ANGIE repo
	 *
	 * @param   string  $platform
	 */
	public static function linkAngiePlatform($platform)
	{
		$commandLine = './link_platform.sh '.$platform;

		$output = '';
		$cwd    = getcwd();
		chdir(__DIR__.'/../../../');
		exec($commandLine, $output);
		chdir($cwd);
	}

	/**
	 * Calls ANGIE script to symlink current repository to test website
	 *
	 * @param   string  $path
	 */
	public static function linkAngieSite($path)
	{
		$commandLine = './link_angie_site.sh '.$path;

		$output = '';
		$cwd    = getcwd();
		chdir(__DIR__.'/../../../');
		exec($commandLine, $output);
		chdir($cwd);
	}

	/**
	 * Recursively remove a directory and all its contents
	 *
	 * @param $directory
	 *
	 * @return bool
	 */
	public static function recursiveRemoveDirectory($directory)
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

			if (is_link($filePath))
			{
				unlink($filePath);

				continue;
			}

			if ($di->isDir())
			{
				static::recursiveRemoveDirectory($filePath);
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

	/**
	 * @param Driver $db
	 * @param        $schema
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public static function populateDatabase(Driver $db, $schema)
	{
		$return = true;

		// Get the contents of the schema file.
		if (!($buffer = file_get_contents($schema)))
		{
			throw new \Exception("Cannot open $schema");
		}

		// Get an array of queries from the schema and process them.
		$queries = static::_splitQueries($buffer);

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
	protected static function _splitQueries($query)
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