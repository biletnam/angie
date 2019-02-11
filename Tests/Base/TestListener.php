<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Base;

use RuntimeException;

/**
 * Customized bootstrap for each test suite.
 *
 * We need a different bootstrap when we are testing Akeeba Solo, Akeeba Backup for WordPress etc. since they run under
 * different environments (standalone, WordPress, ...). PHPUnit only allows a single bootstrap file. To overcome this we
 * need to use a Listener.
 *
 * @see  http://stackoverflow.com/questions/9535078/phpunit-different-bootstrap-for-all-testsuites
 */
class TestListener extends \PHPUnit\Framework\BaseTestListener
{
	/**
	 * A test suite started.
	 *
	 * We hook onto this event to follow a different bootstrap process depending on the test suite currently running.
	 *
	 * @param   \PHPUnit_Framework_TestSuite $suite
	 */
	public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
	{
		/** @var   array $angieTestConfig The test configuration */
		global $angieTestConfig;

		// Make sure that I do get to see all error messages during testing
		if (!defined('AKEEBA_DEBUG'))
		{
			define('AKEEBA_DEBUG', 1);
		}

		$suiteName = $suite->getName();

		// In case we have something like Akeeba\ANGIE\Tests\Solo\Acceptance\ControlPanelTest
		if (substr($suiteName, 0, 18) == 'Akeeba\\ANGIE\\Tests')
		{
			$classParts = explode('\\', $suiteName);
			$suiteName  = $classParts[3];
		}
		// Windows: something like "C:\Users\myUser\SomePath\RepoRootFolder\Tests\Joomla\TestName"
		elseif (substr(PHP_OS, 0, 3) == 'WIN' && stripos($suiteName, '\\Tests\\') !== false)
		{
			list ($junk, $interesting) = explode('\\Tests\\', $suiteName, 2);
			$classParts = explode('\\', $interesting);
			$suiteName  = $classParts[0];
		}
		// Something like "/home/myuser/repos/angie/Tests/WordPress/Acceptance"
		elseif (stripos($suiteName, '/Tests/') !== false)
		{
			list ($junk, $interesting) = explode('/Tests/', $suiteName, 2);
			$classParts = explode('/', $interesting);
			$suiteName  = $classParts[0];
		}

		$suiteName = strtolower($suiteName);

		switch ($suiteName)
		{
			case 'joomla':
				if (defined('ANGIETESTS_JOOMLA'))
				{
					return;
				}

				define('ANGIETESTS_JOOMLA', 1);

				break;

			case 'wordpress':
				if (defined('ANGIETESTS_WORDPRESS'))
				{
					return;
				}

				define('ANGIETESTS_WORDPRESS', 1);

				break;

			default:
				throw new RuntimeException("Unknown test suite name '$suiteName'");
				break;
		}

		$angieTestConfig['site'] = $angieTestConfig[strtolower($suiteName)];

		// Finalize the bootstrap process
		$this->boostrapFinalization($suiteName);
	}

	/**
	 * Asserts the the site's directory exists and is readable
	 *
	 * @param   string $sitePath
	 */
	private function checkSiteDirectory($sitePath)
	{
		if (!is_dir($sitePath))
		{
			throw new RuntimeException("Site root path $sitePath does not exist");
		}

		if (!is_readable($sitePath))
		{
			throw new RuntimeException("Site root path $sitePath is not readable");
		}

	}

	/**
	 * Common finalization of the tests bootstrap process
	 *
	 * @param   string $suiteName The test suite name
	 *
	 * @return  void
	 */
	private function boostrapFinalization($suiteName)
	{
		// Perform the master setup (database tables and so on)
		CommonSetup::masterSetup($suiteName);
	}
}
