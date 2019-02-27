<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Joomla\Acceptance;

use Akeeba\ANGIE\Tests\Base\TestCase\Angie;
use Akeeba\ANGIE\Tests\Engine\Helper\Utils;
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

		$cms_version = $wd->findElement(WebDriverBy::className('angie-cms-version'))->getText();

		$this->assertNotEmpty($cms_version, "CMS version detected by ANGIE shouldn't be empty");
		$this->assertNotEquals('2.5.0', $cms_version, "CMS version shouldn't be the default one");
	}
}
