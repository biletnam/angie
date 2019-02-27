<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Base\TestCase;

use Akeeba\ANGIE\Tests\Base\TestCase\Traits\WebDriver;
use PHPUnit\Framework\TestCase;

class Angie extends TestCase
{
	use WebDriver;

	/**
	 * Initialize the acceptance tests. Connects to the site's backend and logs in a user.
	 */
	public static function setUpBeforeClass()
	{
		global $angieTestConfig;

		if (empty(static::$siteRoot))
		{
			static::$siteRoot = $angieTestConfig['site']['url'];
		}

		static::setupWebDriver();
	}

	/**
	 * At the end of testing close the browser window
	 */
	public static function tearDownAfterClass()
	{
		static::teardownWebDriver();
	}
}
