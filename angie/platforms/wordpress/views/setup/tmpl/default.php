<?php
/**
 * @package   angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer For Joomla!
 */

defined('_AKEEBA') or die();

/** @var $this AngieViewSetup */

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
echo $this->loadAnyTemplate('steps/steps', ['helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-wordpress-setup.html']);
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
				<label for="blogname">
					<?php echo AText::_('SETUP_LBL_SITENAME'); ?>
				</label>
				<input type="text" id="blogname" name="blogname" value="<?php echo $this->stateVars->blogname ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_SITENAME_HELP') ?>
				</span>
			</div>
			<div class="akeeba-form-group">
				<label for="blogdescription">
					<?php echo AText::_('SETUP_LBL_TAGLINE'); ?>
				</label>
				<input type="text" id="blogdescription" name="blogdescription"
					   value="<?php echo $this->stateVars->blogdescription ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_TAGLINE_HELP') ?>
				</span>
			</div>
			<div class="akeeba-form-group">
				<label for="homeurl">
					<?php echo AText::_('SETUP_LBL_WPADDRESS'); ?>
				</label>
				<input type="text" id="homeurl" name="homeurl" value="<?php echo $this->stateVars->homeurl ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_WPADDRESS_HELP') ?>
				</span>
			</div>
			<div class="akeeba-form-group">
				<label for="siteurl">
					<?php echo AText::_('SETUP_LBL_SITEADDRESS'); ?>
				</label>
				<input type="text" id="siteurl" name="siteurl" value="<?php echo $this->stateVars->siteurl ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_SITEADDRESS_HELP') ?>
				</span>
			</div>

			<div class="akeeba-form-group">
				<label for="dbcharset">
					<?php echo AText::_('SETUP_LBL_CHARSET'); ?>
				</label>
				<input type="text" id="dbcharset" name="dbcharset"
					   value="<?php echo $this->stateVars->dbcharset ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_CHARSET_HELP') ?>
				</span>
			</div>

			<div class="akeeba-form-group">
				<label for="dbcollation">
					<?php echo AText::_('SETUP_LBL_COLLATION'); ?>
				</label>
				<input type="text" id="dbcollation" name="dbcollation"
					   value="<?php echo $this->stateVars->dbcollation ?>" />
				<span class="akeeba-help-text" style="display: none">
					<?php echo AText::_('SETUP_LBL_COLLATION_HELP') ?>
				</span>
			</div>
		</div>

		<div>
			<div class="akeeba-panel--orange" style="margin-top: 0">
				<header class="akeeba-block-header">
					<h3><?php echo AText::_('SETUP_HEADER_SERVERCONFIG') ?></h3>
				</header>

				<?php if ($this->hasAutoPrepend): ?>
					<p class="akeeba-block--warning">
						<?php echo AText::sprintf('SETUP_LBL_SERVERCONFIG_AUTOPREPEND_WARN', 'http://php.net/manual/en/ini.core.php#ini.auto-prepend-file') ?>
					</p>
				<?php endif; ?>

				<div class="akeeba-form-group--checkbox--pull-right">
					<label <?php echo $this->auto_prepend['disabled'] ?>>
						<input type="checkbox" value="1" id="disable_autoprepend"
							   name="disable_autoprepend" <?php echo $this->auto_prepend['disabled'] ?> <?php echo $this->auto_prepend['checked'] ?> />
						<?php echo AText::_('SETUP_LBL_SERVERCONFIG_AUTOPREPEND'); ?>
					</label>
				</div>
			</div>

			<!-- Super Administrator settings -->
			<?php if (isset($this->stateVars->superusers)): ?>
			<div class="akeeba-panel--info" style="margin-top: 0">
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
	</div>

	<div style="display: none;">
		<input type="hidden" name="view" value="setup" />
		<input type="hidden" name="task" value="apply" />
	</div>

</form>

<?php if (isset($this->stateVars->superusers)): ?>
<script type="text/javascript">
	setupSuperUsers = <?php echo json_encode($this->stateVars->superusers); ?>;

	akeeba.System.documentReady(function() {
		setupSuperUserChange();
	});
</script>
<?php endif; ?>