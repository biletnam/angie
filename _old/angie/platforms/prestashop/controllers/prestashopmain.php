<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieControllerPrestashopMain extends AngieControllerBaseMain
{
	/**
	 * Try to read the configuration
	 */
	public function getconfig()
	{
		// Load the default configuration and save it to the session
		$data   = $this->input->getData();

        /** @var AngieModelPrestashopConfiguration $model */
        $model = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);
        $this->input->setData($data);
        $this->container->session->saveData();

		$vars = $model->loadFromFile();

		if ($vars)
		{
			foreach ($vars as $k => $v)
			{
				$model->set($k, $v);
			}

			$this->container->session->saveData();

			@ob_clean();
			echo json_encode(true);
		}
		else
		{
			@ob_clean();
			echo json_encode(false);
		}
	}
}
