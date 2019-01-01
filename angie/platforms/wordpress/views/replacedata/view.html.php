<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewReplacedata extends AView
{
	public $replacements = [];

	public $otherTables = [];

	public $prefix = '';

	public $prefixLen = 0;

	public function onBeforeMain()
	{
		$this->container->application->getDocument()->addScript('platform/js/replacedata.js');

		$force = $this->input->getBool('force', false);

		/** @var AngieModelWordpressReplacedata $model */
		$model = $this->getModel();

		$this->replacements = $model->getReplacements(false, $force);
		$this->otherTables  = $model->getNonCoreTables();
		$this->prefix       = $model->getDbo()->getPrefix();
		$this->prefixLen    = strlen($this->prefix);

		return true;
	}
}
