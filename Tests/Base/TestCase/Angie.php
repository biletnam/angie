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
	 * Username for the Akeeba Solo admin user
	 *
	 * @var   string
	 */
	public static $username = 'admin';

	/**
	 * Password for the Akeeba Solo admin user
	 *
	 * @var   string
	 */
	public static $password = 'admin';

	/**
	 * Is this a WordPress test? Set up automatically using the static isWordPress() method.
	 *
	 * @var   bool
	 */
	public $isWordPress = false;

	public function __construct($name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->isWordPress = static::isWordPress();
	}

	public static function isWordPress()
	{
		return false;
	}

	/**
	 * Initialize the acceptance tests. Connects to the site's backend and logs in a user.
	 *
	 * @throws   \Exception
	 * @throws   \Facebook\WebDriver\Exception\TimeOutException
	 */
	public static function setUpBeforeClass()
	{
		global $angieTestConfig;

		if (empty(static::$siteRoot))
		{
			static::$siteRoot = $angieTestConfig['url'];
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
