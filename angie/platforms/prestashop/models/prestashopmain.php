<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelPrestashopMain extends AngieModelBaseMain
{
	/**
	 * Try to detect the Prestashop version in use
	 */
	public function detectVersion()
	{
		$ret = '1.5';

		$filename = APATH_ROOT . '/config/settings.inc.php';

		if (file_exists($filename))
		{
            // There are some defines, but they *shouldn't* create problems
            include_once $filename;

            if(defined('_PS_VERSION_'))
            {
                $ret = _PS_VERSION_;
            }
		}

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
            $minPHPVersion = '5.3.4';

            $phpOptions[] = array (
                'label'		=> AText::sprintf('MAIN_LBL_REQ_PHP_VERSION', $minPHPVersion),
                'current'	=> version_compare(phpversion(), $minPHPVersion, 'ge'),
                'warning'	=> false,
            );

            $phpOptions[] = array (
                'label'		=> AText::_('MAIN_LBL_REQ_MCGPCOFF'),
                'current'	=> (ini_get('magic_quotes_gpc') == false),
                'warning'	=> false,
            );

            $phpOptions[] = array (
                'label'		=> AText::_('MAIN_LBL_REQ_REGGLOBALS'),
                'current'	=> (ini_get('register_globals') == false),
                'warning'	=> false,
            );

            $phpOptions[] = array (
                'label'		=> AText::_('MAIN_LBL_REQ_ZLIB'),
                'current'	=> extension_loaded('zlib'),
                'warning'	=> false,
            );

            $phpOptions[] = array (
                'label'		=> AText::_('MAIN_LBL_REQ_XML'),
                'current'	=> extension_loaded('xml'),
                'warning'	=> false,
            );

            $phpOptions[] = array (
                'label'		=> AText::_('MAIN_LBL_REQ_DATABASE'),
                'current'	=> (function_exists('mysql_connect') || function_exists('mysqli_connect') || function_exists('pg_connect') || function_exists('sqlsrv_connect')),
                'warning'	=> false,
            );

            if (extension_loaded( 'mbstring' ))
            {
                $option = array (
                    'label'		=> AText::_( 'MAIN_REQ_MBLANGISDEFAULT' ),
                    'current'	=> (strtolower(ini_get('mbstring.language')) == 'neutral'),
                    'warning'	=> false,
                );
                $option['notice'] = $option['current'] ? null : AText::_('MAIN_MSG_NOTICEMBLANGNOTDEFAULT');
                $phpOptions[] = $option;

                $option = array (
                    'label'		=> AText::_('MAIN_REQ_MBSTRINGOVERLOAD'),
                    'current'	=> (ini_get('mbstring.func_overload') == 0),
                    'warning'	=> false,
                );
                $option['notice'] = $option['current'] ? null : AText::_('MAIN_MSG_NOTICEMBSTRINGOVERLOAD');
                $phpOptions[] = $option;
            }

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

            $phpOptions[] = array (
                'label'			=> AText::_('MAIN_LBL_REQ_GD_LIBRARY'),
                'current'		=> function_exists('imagecreatetruecolor'),
                'warning'	=> true,
            );

            $phpOptions[] = array (
                'label'			=> AText::_('MAIN_LBL_REQ_FILEUPLOAD'),
                'current'		=> ini_get('file_uploads'),
                'warning'	=> true,
            );

            $cW = (@ file_exists('../config/settings.inc.php') && @is_writable('../config/settings.inc.php')) || @is_writable('../config');
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
                'label'			=> AText::_('MAIN_REC_SAFEMODE'),
                'current'		=> (bool) ini_get('safe_mode'),
                'recommended'	=> false,
            );

            $phpOptions[] = array(
                'label'			=> AText::_('MAIN_REC_DISPERRORS'),
                'current'		=> (bool) ini_get('display_errors'),
                'recommended'	=> false,
            );

            $phpOptions[] = array(
                'label'			=> AText::_('MAIN_REC_MCR'),
                'current'		=> (bool) ini_get('magic_quotes_runtime'),
                'recommended'	=> false,
            );

            $phpOptions[] = array(
                'label'			=> AText::_('MAIN_REC_MCGPC'),
                'current'		=> (bool) ini_get('magic_quotes_gpc'),
                'recommended'	=> false,
            );

            $phpOptions[] = array(
                'label'			=> AText::_('MAIN_REC_OUTBUF'),
                'current'		=> (bool) ini_get('output_buffering'),
                'recommended'	=> false,
            );

            $phpOptions[] = array(
                'label'			=> AText::_('MAIN_REC_SESSIONAUTO'),
                'current'		=> (bool) ini_get('session.auto_start'),
                'recommended'	=> false,
            );

            $phpOptions[] = array(
                'label'			=> AText::_('MAIN_REC_CURL'),
                'current'		=> function_exists('curl_init'),
                'recommended'	=> true,
            );

            $phpOptions[] = array(
                'label'			=> AText::_('MAIN_REC_FTP'),
                'current'		=> function_exists('ftp_connect'),
                'recommended'	=> true,
            );

            $phpOptions[] = array (
                'label'			=> AText::_('MAIN_REC_SSH2'),
                'current'		=> extension_loaded('ssh2'),
                'recommended'	=> true,
            );

            $phpOptions[] = array (
                'label'			=> AText::_('MAIN_REC_FOPEN'),
                'current'		=> ini_get('allow_url_fopen'),
                'recommended'	=> true,
            );

            if (function_exists('gzencode'))
            {
                $gz = @gzencode('dd') !== false;
            }
            else
            {
                $gz = false;
            }

            $phpOptions[] = array (
                'label'			=> AText::_('MAIN_REC_GZ'),
                'current'		=> $gz,
                'recommended'	=> true,
            );

            $phpOptions[] = array (
                'label'			=> AText::_('MAIN_REC_MCRYPT'),
                'current'		=> function_exists('mcrypt_encrypt'),
                'recommended'	=> true,
            );

            $phpOptions[] = array (
                'label'			=> AText::_('MAIN_REC_DOM'),
                'current'		=> extension_loaded('Dom'),
                'recommended'	=> true,
            );
        }

		return $phpOptions;
	}
}
