<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Joomla\Acceptance;

use Akeeba\ANGIE\Tests\Base\TestCase\Angie;
use Akeeba\ANGIE\Tests\Engine\Helper\Utils;

class MainPageTest extends Angie
{
	public static function setUpBeforeClass()
	{
		global $angieTestConfig;

		parent::setUpBeforeClass();

		// Check if we have to extract the site
		if (static::$siteRoot.'/index.php')
		{
			Utils::extractArchive(__DIR__.'/../../_data/archives/joomla.jpa', $angieTestConfig['angie']['root']);
		}

		// Always relink. It won't hurt and it's very fast to do
		Utils::linkAngieSite($angieTestConfig['angie']['root']);
	}

	public function testMainPageLayout()
	{
		// Dummy test just to check everything is working fine
		$this->assertTrue(true);
	}
}
