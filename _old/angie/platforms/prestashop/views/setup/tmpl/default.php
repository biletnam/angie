<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

/** @var $this AView */

$document = $this->container->application->getDocument();

$document->addScript('angie/js/json.js');
$document->addScript('angie/js/ajax.js');
$document->addScript('platform/js/setup.js');
$url = 'index.php';
$document->addScriptDeclaration(<<<JS
var akeebaAjax = null;

akeeba.System.documentReady(function(){
	akeebaAjax = new akeebaAjaxConnector('$url');
});

JS
);

$this->loadHelper('select');

echo $this->loadAnyTemplate('steps/buttons');
echo $this->loadAnyTemplate('steps/steps', ['helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-prestashop-setup.html']);
?>
<form name="setupForm" action="index.php" method="post" class="akeeba-form--horizontal">
	<div>
		<button class="akeeba-btn--dark" style="float: right;" onclick="toggleHelp(); return false;">
			<span class="akion-help"></span>
			Show / hide help
		</button>
	</div>

	<div class="akeeba-container--50-50">

		<!-- Site parameters -->
		<div class="akeeba-panel--teal" style="margin-top: 0">
			<header class="akeeba-block-header">
				<h3><?php echo AText::_('SETUP_HEADER_SITEPARAMS') ?></h3>
			</header>
			<div class="akeeba-form-group">
				<label for="sitename">
					<?php echo AText::_('SETUP_LBL_SITENAME'); ?>
				</label>
				<input type="text" id="sitename" name="sitename"
					   value="<?php echo $this->stateVars->sitename ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_SITENAME_HELP') ?>
				</span>
			</div>
			<div class="akeeba-form-group">
				<label for="siteurl">
					<?php echo AText::_('SETUP_LBL_LIVESITE'); ?>
				</label>
				<input type="text" id="siteurl" name="siteurl"
					   value="<?php echo $this->stateVars->siteurl ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_LIVESITE_HELP') ?>
				</span>
			</div>
		</div>

		<?php if (isset($this->stateVars->superusers)): ?>
			<!-- Super Administrator settings -->
			<div class="akeeba-panel--info">
				<header class="akeeba-block-header">
					<h3><?php echo AText::_('SETUP_HEADER_SUPERUSERPARAMS') ?></h3>
				</header>

				<div class="akeeba-form-group">
					<label for="superuserid">
						<?php echo AText::_('SETUP_LABEL_SUPERUSER'); ?>
					</label>
					<?php echo AngieHelperSelect::superusers(); ?>
					<span class="akeeba-help-text" style="display: none">
						<?php echo AText::_('SETUP_LABEL_SUPERUSER_HELP') ?>
					</span>
				</div>
				<div class="akeeba-form-group">
					<label for="superuseremail">
						<?php echo AText::_('SETUP_LABEL_SUPERUSEREMAIL'); ?>
					</label>
					<input type="text" id="superuseremail" name="superuseremail" value="" />
					<span class="akeeba-help-text" style="display: none">
						<?php echo AText::_('SETUP_LABEL_SUPERUSEREMAIL_HELP') ?>
					</span>
				</div>
				<div class="akeeba-form-group">
					<label for="superuserpassword">
						<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORD'); ?>
					</label>
					<input type="password" id="superuserpassword" name="superuserpassword" value="" />
					<span class="akeeba-help-text" style="display: none">
						<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORD_HELP2') ?>
					</span>
				</div>
				<div class="akeeba-form-group">
					<label for="superuserpasswordrepeat">
						<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORDREPEAT'); ?>
					</label>
					<input type="password" id="superuserpasswordrepeat" name="superuserpasswordrepeat"
						   value="" />
					<span class="akeeba-help-text" style="display: none">
						<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORDREPEAT_HELP') ?>
					</span>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<div>
		<input type="hidden" name="view" value="setup" />
		<input type="hidden" name="task" value="apply" />
	</div>

</form>

<?php if (isset($this->stateVars->superusers)): ?>
<script type="text/javascript">
	setupSuperUsers = <?php echo json_encode($this->stateVars->superusers); ?>;
	$(document).ready(function () {
		setupSuperUserChange();
	});
</script>
<?php endif; ?>