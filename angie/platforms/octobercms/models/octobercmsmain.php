<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2017 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelOctobercmsMain extends AngieModelBaseMain
{
	/**
	 * Try to detect the October CMS version in use
	 */
	public function detectVersion()
	{
		// The version is stored inside the database. This means that we have to restore the
		// database in order to see it, and that's something we can't do at this stage
		$ret = '0.0.0';

		$this->container->session->set('version', $ret);
		$this->container->session->saveData();
	}

	/**
	 * Get the required settings analysis
	 *
	 * @return  array
	 */
	public function getRequired()
	{
		static $phpOptions = array();

		if (empty($phpOptions))
		{
			$minPHPVersion = '5.5.9';

			$phpOptions[] = array (
				'label'		=> AText::sprintf('MAIN_LBL_REQ_PHP_VERSION', $minPHPVersion),
				'current'	=> version_compare(phpversion(), $minPHPVersion, 'ge'),
				'warning'	=> false,
			);

			$phpOptions[] = array(
				'label'		=> AText::_('MAIN_REC_CURL'),
				'current'	=> function_exists('curl_init'),
				'warning'	=> false,
			);

			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_ZLIB'),
				'current'	=> extension_loaded('zlib'),
				'warning'	=> false,
			);

			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_DATABASE'),
				'current'	=> defined('PDO::ATTR_DRIVER_NAME'),
				'warning'	=> false,
			);

			$phpOptions[] = array(
				'label'		=> AText::_('MAIN_LBL_REQ_GD'),
				'current'	=> extension_loaded('gd'),
				'warning'	=> false,
			);

			$phpOptions[] = array(
				'label'		=> AText::_('MAIN_LBL_REQ_MCRYPT'),
				'current'	=> extension_loaded('mcrypt'),
				'warning'	=> false,
			);

			$phpOptions[] = array(
				'label'		=> AText::_('MAIN_LBL_REQ_MBSTRING'),
				'current'	=> extension_loaded('mbstring'),
				'warning'	=> false,
			);

			$phpOptions[] = array(
				'label'		=> AText::_('MAIN_LBL_REQ_DOM'),
				'current'	=> extension_loaded('dom'),
				'warning'	=> false,
			);

			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_INIPARSER'),
				'current'	=> $this->getIniParserAvailability(),
				'warning'	=> false,
			);

			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_JSON'),
				'current'	=> function_exists('json_encode') && function_exists('json_decode'),
				'warning'	=> false,
			);

			$cW = (@ file_exists('../app/system/config.php') && @is_writable('../app/system/config.php')) || @is_writable('../');
			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_CONFIGURATIONPHP'),
				'current'	=> $cW,
				'notice'	=> $cW ? null : AText::_('MAIN_MSG_CONFIGURATIONPHP'),
				'warning'	=> true
			);
		}

		return $phpOptions;
	}

	public function getRecommended()
	{
		static $phpOptions = array();

		if (empty($phpOptions))
		{
			$phpOptions[] = array(
				'label'			=> AText::_('MAIN_REC_DISPERRORS'),
				'current'		=> (bool) ini_get('display_errors'),
				'recommended'	=> false,
			);

			$phpOptions[] = array(
				'label'			=> AText::_('MAIN_REC_UPLOADS'),
				'current'		=> (bool) ini_get('file_uploads'),
				'recommended'	=> true,
			);

			$phpOptions[] = array(
				'label'			=> AText::_('MAIN_REC_NATIVEZIP'),
				'current'		=> function_exists('zip_open') && function_exists('zip_read'),
				'recommended'	=> true,
			);

		}

		return $phpOptions;
	}
}