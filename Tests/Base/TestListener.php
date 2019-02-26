<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Base;

use Akeeba\ANGIE\Tests\Base\TestCase\Traits\WebDriver;
use Akeeba\ANGIE\Tests\Engine\Platform\Joomla;
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
	use WebDriver;

	/**
	 * A test suite started.
	 *
	 * We hook onto this event to follow a different bootstrap process depending on the test suite currently running.
	 *
	 * @param   \PHPUnit_Framework_TestSuite $suite
	 *
	 * @throws \Exception
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
		$platform  = null;

		switch ($suiteName)
		{
			case 'joomla':
				if (defined('ANGIETESTS_JOOMLA'))
				{
					return;
				}

				define('ANGIETESTS_JOOMLA', 1);

				$platform = new Joomla();

				if (empty($platform->packages))
				{
					throw new \RuntimeException("No Joomla! packages found. Please place the .zip full installation J! packages in the inbox directory.");
				}

				break;

			case 'wordpress':
				if (defined('ANGIETESTS_WORDPRESS'))
				{
					return;
				}

				define('ANGIETESTS_WORDPRESS', 1);

				break;

			case 'solo':
				if (defined('ANGIETESTS_SOLO'))
				{
					return;
				}

				define('ANGIETESTS_SOLO', 1);

				break;

			default:
				throw new RuntimeException("Unknown test suite name '$suiteName'");
				break;
		}

		$angieTestConfig['site'] = $angieTestConfig['testplatforms'][strtolower($suiteName)];

		// No root file? Let's create the site, then
		if (!file_exists($angieTestConfig['site']['root']))
		{
			$platform->createSite();
		}

		self::setupWebDriver();

		// Let's check if we have to actually install Akeeba Backup on that site
		if ($platform->akeebaNeedsInstall())
		{
			// Get the extension package (building it if required) and then install it
			$zipPath = $platform->getExtensionZip();
			$platform->installExtension(self::$wd, $zipPath);
		}

		// Check if we have a backup of such platform in our _data/archives folder. If not, trigger a CLI backup
		if (!file_exists(__DIR__ . '/../_data/archives/'.strtolower($suiteName).'.jpa'))
		{
			$platform->takeCliBackup();
		}
	}
}
