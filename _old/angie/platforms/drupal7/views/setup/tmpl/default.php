<?php
/**
 * @package   angi4j
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer For Joomla!
 */

defined('_AKEEBA') or die();

/** @var $this AView */

$document = $this->container->application->getDocument();

$document->addScript('angie/js/json.js');
$document->addScript('angie/js/ajax.js');
$document->addScript('angie/js/polyfills.js');
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
echo $this->loadAnyTemplate('steps/steps', ['helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-drupal-setup.html']);

$key = str_replace('.', '_', $this->input->getCmd('substep', 'default'));

?>
<?php
// The modal window is displayed only when we have a multi site environment and we have to change the settings.php
// file multiple times
?>
<div id="restoration-dialog" style="display: none;">
	<div class="akeeba-renderer-fef" style="max-height: 500px;">
		<h4><?php echo AText::_('SETUP_HEADER_UPDATE') ?></h4>

		<div id="restoration-progress">
			<div class="akeeba-progress">
				<div class="akeeba-progress-fill" id="restoration-progress-bar" style="width: 40%;"></div>
			</div>
		</div>
		<div id="restoration-success">
			<div class="akeeba-block--success">
				<?php echo AText::_('SETUP_HEADER_SUCCESS'); ?>
			</div>
			<p>
				<?php echo AText::_('SETUP_MSG_SUCCESS'); ?>
			</p>
			<button type="button" onclick="setupBtnSuccessClick(); return false;" class="akeeba-btn--green">
				<span class="akion-arrow-right-c"></span>
				<?php echo AText::_('SETUP_BTN_SUCCESS'); ?>
			</button>
		</div>
		<div id="restoration-error">
			<div class="akeeba-block--failure">
				<?php echo AText::_('SETUP_HEADER_ERROR'); ?>
			</div>
			<div class="akeeba-panel--info" id="restoration-lbl-error">

			</div>

			<textarea id="restoration-config"
					  style="line-height: normal;width:100%;display:none;height:150px"></textarea>

			<button id="nextStep" style="display:none" type="button" onclick="setupBtnSuccessClick(); return false;"
					class="akeeba-btn--green">
				<span class="akion-arrow-right-c"></span>
				<?php echo AText::_('SETUP_BTN_SUCCESS'); ?>
			</button>
		</div>
	</div>
</div>

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
				<input type="text" id="sitename" name="<?php echo $key . '_' ?>sitename"
					   value="<?php echo $this->stateVars->sitename ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_SITENAME_HELP') ?>
				</span>
			</div>
			<div class="akeeba-form-group">
				<label for="siteemail">
					<?php echo AText::_('SETUP_LBL_SITEEMAIL'); ?>
				</label>
				<input type="text" id="siteemail" name="<?php echo $key . '_' ?>siteemail"
					   value="<?php echo $this->stateVars->siteemail ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_SITEEMAIL_HELP') ?>
				</span>
			</div>
			<div class="akeeba-form-group">
				<label for="livesite">
					<?php echo AText::_('SETUP_LBL_LIVESITE'); ?>
				</label>
				<input type="text" id="livesite" name="<?php echo $key . '_' ?>livesite"
					   value="<?php echo $this->stateVars->livesite ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_LIVESITE_HELP') ?>
				</span>
			</div>
			<div class="akeeba-form-group">
				<label for="cookiedomain">
					<?php echo AText::_('SETUP_LBL_COOKIEDOMAIN'); ?>
				</label>
				<input type="text" id="cookiedomain" name="<?php echo $key . '_' ?>cookiedomain"
					   value="<?php echo $this->stateVars->cookiedomain ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_COOKIEDOMAIN_HELP') ?>
				</span>
			</div>
		</div>

		<!-- Super Admin and Fine Tuning -->
		<div>
			<?php if (isset($this->stateVars->superusers)): ?>
				<!-- Super Administrator settings -->
				<div class="akeeba-panel--info" style="margin-top: 0">
					<header class="akeeba-block-header">
						<h3><?php echo AText::_('SETUP_HEADER_SUPERUSERPARAMS') ?></h3>
					</header>

					<div class="akeeba-form-group">
						<label for="superuserid">
							<?php echo AText::_('SETUP_LABEL_SUPERUSER'); ?>
						</label>
						<?php echo AngieHelperSelect::superusers(null, $key . '_superuserid'); ?>
						<span class="akeeba-help-text" style="display: none">
							<?php echo AText::_('SETUP_LABEL_SUPERUSER_HELP') ?>
						</span>
					</div>
					<div class="akeeba-form-group">
						<label for="superuseremail">
							<?php echo AText::_('SETUP_LABEL_SUPERUSEREMAIL'); ?>
						</label>
						<input type="text" id="superuseremail" name="<?php echo $key . '_' ?>superuseremail" value="" />
						<span class="akeeba-help-text" style="display: none">
							<?php echo AText::_('SETUP_LABEL_SUPERUSEREMAIL_HELP') ?>
						</span>
					</div>
					<div class="akeeba-form-group">
						<label for="superuserpassword">
							<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORD'); ?>
						</label>
						<input type="password" id="superuserpassword" name="<?php echo $key . '_' ?>superuserpassword"
							   value="" />
						<span class="akeeba-help-text" style="display: none">
							<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORD_HELP2') ?>
						</span>
					</div>
					<div class="akeeba-form-group">
						<label for="superuserpasswordrepeat">
							<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORDREPEAT'); ?>
						</label>
						<input type="password" id="superuserpasswordrepeat"
							   name="<?php echo $key . '_' ?>superuserpasswordrepeat" value="" />
						<span class="akeeba-help-text" style="display: none">
							<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORDREPEAT_HELP') ?>
						</span>
					</div>

					<input type="hidden" id="hash" name="<?php echo $key ?>_hash" value="" />
				</div>
			<?php endif; ?>

			<!-- Fine-tuning -->
			<div class="akeeba-panel--info" style="margin-top: 0">
				<header class="akeeba-block-header">
					<h3><?php echo AText::_('SETUP_HEADER_FINETUNING') ?></h3>
				</header>

				<div class="akeeba-form-group">
					<label for="siteroot">
						<?php echo AText::_('SETUP_LABEL_SITEROOT'); ?>
					</label>
					<input type="text" disabled="disabled" id="siteroot"
						   value="<?php echo $this->stateVars->site_root_dir ?>" />
					<span class="akeeba-help-text" style="display: none">
						<?php echo AText::_('SETUP_LABEL_SITEROOT_HELP') ?>
					</span>
				</div>
				<div class="akeeba-form-group">
					<label for="tmppath">
						<?php echo AText::_('SETUP_LABEL_TMPPATH'); ?>
					</label>
					<input type="text" id="tmppath" name="<?php echo $key . '_' ?>tmppath"
						   value="<?php echo $this->stateVars->tmppath ?>" />
					<span class="akeeba-help-text" style="display: none">
						<?php echo AText::_('SETUP_LABEL_TMPPATH_HELP') ?>
					</span>
				</div>

			</div>
		</div>
	</div>

	<div style="display: none;">
		<input type="hidden" name="view" value="setup" />
		<input type="hidden" name="task" value="apply" />
		<input type="hidden" name="format" value="" />
		<input type="hidden" name="substep" value="<?php echo $key ?>" />
	</div>

</form>

<script type="text/javascript">
	<?php if (isset($this->stateVars->superusers)): ?>
	setupSuperUsers = <?php echo json_encode($this->stateVars->superusers); ?>;
	<?php endif; ?>

	akeeba.System.documentReady(function() {
		<?php if (isset($this->stateVars->superusers)): ?>
		setupSuperUserChange();
		<?php endif; ?>
		setupDefaultTmpDir  = '<?php echo addcslashes($this->stateVars->default_tmp, '\\') ?>';
		setupDefaultLogsDir = '<?php echo addcslashes($this->stateVars->default_log, '\\') ?>';
	});
</script>
