<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Base\TestCase\Traits;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverPlatform;

/**
 * Adds Selenium Web Driver capabilities to the object
 */
trait WebDriver
{
	/**
	 * The Selenium WebDriver interface
	 *
	 * @see   https://github.com/facebook/php-webdriver/wiki
	 * @see   http://codeception.com/11-12-2013/working-with-phpunit-and-selenium-webdriver.html
	 * @see   https://gist.github.com/aczietlow/7c4834f79a7afd920d8f
	 *
	 * @var   RemoteWebDriver
	 */
	protected static $wd;

	/**
	 * The URL to the Selenium Server web driver interface
	 *
	 * @var   string
	 */
	protected static $seleniumServerURL = 'http://localhost:4444/wd/hub';

	/**
	 * The site's root. Automatically loaded from the test configuration.
	 *
	 * @var   string
	 */
	protected static $siteRoot;

	/**
	 * Setup a Web Driver connector
	 *
	 * @param   array                    $capabilities
	 * @param   null                     $connection_timeout_in_ms
	 * @param   null                     $request_timeout_in_ms
	 * @param   null                     $http_proxy
	 * @param   null                     $http_proxy_port
	 * @param   DesiredCapabilities|null $required_capabilities
	 */
	protected static function setupWebDriver(array $capabilities = array(), $connection_timeout_in_ms = null,
	                                         $request_timeout_in_ms = null,
	                                         $http_proxy = null,
	                                         $http_proxy_port = null,
	                                         DesiredCapabilities $required_capabilities = null)
	{
		if (is_object(self::$wd) && (self::$wd instanceof RemoteWebDriver))
		{
			self::$wd->quit();
			self::$wd = null;
		}

		self::$siteRoot = rtrim(self::$siteRoot, '/') . '/';

		$capabilities = array_merge([
			WebDriverCapabilityType::BROWSER_NAME => WebDriverBrowserType::CHROME,
			WebDriverCapabilityType::PLATFORM => WebDriverPlatform::ANY,
		], $capabilities);
		;

		self::$wd = RemoteWebDriver::create(self::$seleniumServerURL, $capabilities, $connection_timeout_in_ms,
			$request_timeout_in_ms, $http_proxy, $http_proxy_port, $required_capabilities);
	}

	/**
	 * Tears down the web driver
	 */
	protected static function teardownWebDriver()
	{
		if (is_object(self::$wd) && (self::$wd instanceof RemoteWebDriver))
		{
			try
			{
				self::$wd->quit();
			}
			catch (\Exception $e)
			{
				// Sometimes the webdriver fails to quit cleanly, bubbling up an exception, resulting in "polluted" tests:
				// Tests are successful but you see an exception in the terminal, so you'll end up spending half morning
				// to debug it just to discover that's something out of our control.
				// So let's spend that time for things that matter, shall we?
			}
		}

		self::$wd = null;
	}

	/**
	 * Take a screenshot of the currently displayed page in the test browser. The screenshot is in PNG format and it's
	 * named after the class and method that called us.
	 *
	 * Any $suffix provided will be appended after the filename, prefixed with an underscore. This is useful to
	 * distinguish between screenshots taken with different data sets.
	 *
	 * @param   null|string $suffix The file name suffix
	 */
	public function screenshot($suffix = null)
	{
		$callstack    = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$caller       = $callstack[1];
		$parentCaller = $callstack[2];

		if ($parentCaller['function'] == $caller['function'])
		{
			$caller = $parentCaller;
		}

		self::_doTakeScreenshot($caller, $suffix);
	}

	/**
	 * Static version of screenshot()
	 *
	 * @param   null $suffix
	 */
	public static function screenshotStatic($suffix = null)
	{
		$callstack    = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$caller       = $callstack[1];
		$parentCaller = $callstack[2];

		if ($parentCaller['function'] == $caller['function'])
		{
			$caller = $parentCaller;
		}

		self::_doTakeScreenshot($caller, $suffix);
	}

	/**
	 * The actual implementation of the screenshot method
	 *
	 * @param   array $caller
	 * @param   null  $suffix
	 */
	private static function _doTakeScreenshot($caller, $suffix = null)
	{
		$wd         = self::$wd;
		$path       = __DIR__ . '/../../../screenshots';
		$classParts = explode('\\', $caller['class']);
		// Remove the Akeeba\Backup\Tests part from the class
		$classParts = array_slice($classParts, 3);

		if (count($classParts) > 1)
		{
			foreach ($classParts as $part)
			{
				$path .= '/' . $part;

				if (!is_dir($path))
				{
					mkdir($path, 0755, true);
				}
			}
		}

		$filename = $caller['function'];

		if ($suffix)
		{
			$filename .= '_' . $suffix;
		}

		$wd->takeScreenshot($path . '/' . $filename . '.png');
	}
}
