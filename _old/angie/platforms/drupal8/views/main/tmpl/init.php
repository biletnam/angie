<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/** @var  AngieViewMain  $this */

defined('_AKEEBA') or die();

echo $this->loadAnyTemplate('steps/steps', array(
	'helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-drupal.html#angie-drupal-first',
	'videourl' => 'https://www.akeebabackup.com/videos/1214-akeeba-solo/1637-abts05-restoring-site-new-server.html'
));
?>

<?php if (!$this->reqMet): ?>
<div class="akeeba-block--failure">
	<?php echo AText::_('MAIN_LBL_REQUIREDREDTEXT'); ?>
</div>
<?php endif; ?>

<div class="akeeba-container--50-50">
	<?php echo $this->loadAnyTemplate('init/panel_required', []); ?>
	<?php echo $this->loadAnyTemplate('init/panel_recommended', []); ?>
</div>

<div class="akeeba-container--50-50">
	<?php echo $this->loadAnyTemplate('init/panel_backupinfo', []); ?>
	<?php echo $this->loadAnyTemplate('init/panel_serverinfo', []); ?>
</div>
