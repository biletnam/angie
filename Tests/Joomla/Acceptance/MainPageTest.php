<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Joomla\Acceptance;

use Akeeba\ANGIE\Tests\Base\TestCase\Angie;
use Akeeba\ANGIE\Tests\Engine\Helper\Utils;
use DirectoryIterator;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MainPageTest extends Angie
{
	public static function setUpBeforeClass()
	{
		global $angieTestConfig;

		parent::setUpBeforeClass();

		// Check if we have to extract the site
		if (!file_exists($angieTestConfig['angie']['root'].'/index.php'))
		{
			Utils::extractArchive(__DIR__.'/../../_data/archives/joomla.jpa', $angieTestConfig['angie']['root']);
		}

		// Always relink. It won't hurt and it's very fast to do
		Utils::linkAngieSite($angieTestConfig['angie']['root']);
	}

	public function testMainPageLayout()
	{
		$wd = static::$wd;

		$this->loadMainPage();

		$cms_version = $wd->findElement(WebDriverBy::className('angie-cms-version'))->getText();

		$this->assertNotEmpty($cms_version, "CMS version detected by ANGIE shouldn't be empty");
		$this->assertNotEquals('2.5.0', $cms_version, "CMS version shouldn't be the default one");
	}

	public function testStartOverButton()
	{
		global $angieTestConfig;

		// Recicle the session folder, just in case
		Utils::deleteAngieSessionData();

		$wd = static::$wd;

		$this->loadMainPage();

		// Let's see enumerate the session storage files
		$folder        = $angieTestConfig['angie']['root'].'/installation/tmp';
		$di            = new DirectoryIterator($folder);
		$session_files = [];

		foreach ($di as $file)
		{
			// Get ANGIE session files
			if (strpos($file->getFilename(), 'storagedata') === 0)
			{
				$session_files[] = [
					'file' => $file->getPathname(),
					'data' => md5(file_get_contents($file->getPathname()))
				];
			}
		}

		$this->assertEquals(1, count($session_files), 'There must be only 1 session file ');

		$session_file  = $session_files[0]['file'];
		$original_data = $session_files[0]['data'];

		// Got original session data. Now let's modify some values in the session. The easiest way it to attemp to restore
		// the database, but pass invalid data so info will be stored in the session

		$wd->get(static::$siteRoot.'/installation/index.php?view=database');

		// Put anything in the database host field
		$wd->findElement(WebDriverBy::id('dbhost'))->sendKeys('somethingsomething');
		$wd->findElement(WebDriverBy::id('btnNext'))->click();

		sleep(1);

		// Now let's double check that data was actually stored inside the session
		$new_data = md5(file_get_contents($session_file));

		$this->assertNotEquals($original_data, $new_data, 'ANGIE session data should be different after trying to restore the db');

		// Finally hit the startover button. Session data should be equal to the original one
		$this->loadMainPage();

		$wd->findElement(WebDriverBy::id('startover'))->click();
		$wd->wait(10)->until(
			WebDriverExpectedCondition::elementTextContains(
				WebDriverBy::id('mainContent'), 'Recommended settings'
			)
		);

		$nuked_data = md5(file_get_contents($session_file));

		$this->assertEquals($original_data, $nuked_data, 'Session data should be the same of the starting one after hitting the "Start Over" button');

		// Nuke everything, just in case
		Utils::deleteAngieSessionData();
	}

	private function loadMainPage()
	{
		$wd = static::$wd;

		$wd->get(static::$siteRoot.'/installation/index.php');

		// This wait will serve two purposes: wait until the AJAX call is completed AND check if we have any untraslated strings
		try
		{
			$wd->wait(10)->until(
				WebDriverExpectedCondition::elementTextContains(
					WebDriverBy::id('mainContent'), 'Recommended settings'
				)
			);
		}
		catch (\RuntimeException $e)
		{
			$this->fail('ANGIE Main page did not properly initialize.');
		}
	}
}
