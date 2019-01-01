<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/** @var  AngieViewMain  $this */

defined('_AKEEBA') or die();

echo $this->loadAnyTemplate('steps/steps', array('helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-misc.html#angie-misc-first'));
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

    <div class="akeeba-panel--info">
        <header class="akeeba-block-header">
            <h3><?php echo AText::_('MAIN_HEADER_SITEINFO') ?></h3>
        </header>
        <table class="akeeba-table--striped" width="100%">
			<tbody>
				<tr>
					<td>
						<label><?php echo AText::_('MAIN_LBL_SITE_PHP') ?></label>
					</td>
					<td><?php echo PHP_VERSION ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
