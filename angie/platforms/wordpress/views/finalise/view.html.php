<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewFinalise extends AView
{
	public $showconfig;
	public $configuration;

	/** @var array Did we get any warning during the replace step? If so, warn the user */
	public $replace_warnings = array();

	/** @var bool The user disabled auto-prepend scripts? If so, warn him to re-enable them */
	public $autoprepend_disabled = false;

	public function onBeforeMain()
	{
        $this->container->application->getDocument()->addScript('platform/js/finalise_wp.js');

		$writtenConfiguration = $this->container->session->get('writtenConfiguration', true);
		$this->showconfig 	  = !$writtenConfiguration;

		if ($this->showconfig)
		{
			/** @var AngieModelWordpressConfiguration $configurationModel */
			$configurationModel = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);
			$this->configuration = $configurationModel->getFileContents();
		}

		$this->autoprepend_disabled = $this->container->session->get('autoprepend_disabled', false);
		$this->replace_warnings = $this->container->session->get('replacedata.warnings', array());

		return true;
	}
}
