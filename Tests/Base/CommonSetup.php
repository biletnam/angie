<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Base;

use DirectoryIterator;

abstract class CommonSetup
{
	/** @var   array  Known users we have already created */
	protected static $users = [];

	/**
	 * Initialisation of the site for testing purposes
	 */
	public static function masterSetup($testSuite)
	{
		// Recycle the screenshots folder
		self::recycleScreenshotsFolder();
	}

	/**
	 * Recycle the folder containing the screenshots
	 *
	 * @return  void
	 */
	protected static function recycleScreenshotsFolder()
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
				self::rmdir($file->getPathname());
			}
		}
	}

	/**
	 * Remove a folder recursively
	 *
	 * @param   string $dirName The folder to delete
	 *
	 * @return  bool  True in success
	 */
	private static function rmdir($dirName)
	{
		if (!is_dir($dirName))
		{
			return @unlink($dirName);
		}

		$ret = true;
		$di  = new \DirectoryIterator($dirName);

		/** @var \DirectoryIterator $dirEntry */
		foreach ($di as $dirEntry)
		{
			if ($dirEntry->isDot())
			{
				continue;
			}

			if ($dirEntry->isFile())
			{
				$ret = $ret && @unlink($dirEntry->getPathname());
			}
			elseif ($dirEntry->isDir())
			{
				$ret = $ret && self::rmdir($dirEntry->getPathname());
			}
		}

		$ret = $ret && @rmdir($dirName);

		return $ret;
	}
}
