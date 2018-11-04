<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

/** @var $this AView */

$document = $this->container->application->getDocument();

$document->addScript('angie/js/json.js');
$document->addScript('angie/js/ajax.js');
$document->addScript('angie/js/finalise.js');

$url = 'index.php';
$js = <<<JS
var akeebaAjax = null;

akeeba.System.documentReady(function(){
	akeebaAjax = new akeebaAjaxConnector('$url');

	if ((window.name == 'installer'))
	{
		document.getElementById('finaliseKickstart').style.display = 'block';
	}
	else if ((window.name == 'abinstaller') || (window.name == 'solo_angie_window'))
	{
		document.getElementById('finaliseIntegrated').style.display = 'block';
	}
	else
	{
		document.getElementById('finaliseStandalone').style.display = 'block';
	}
});
JS;

$document->addScriptDeclaration($js);

echo $this->loadAnyTemplate('steps/buttons');
echo $this->loadAnyTemplate('steps/steps', array('helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-installers.html#angie-common-finalise'));
?>

<div id="finalisationSteps" class="akeeba-panel--info">
	<header class="akeeba-block-header">
		<h2>
			<?php echo AText::_('SETUP_LBL_FINALISATION_STEPS')?>
		</h2>
	</header>

	<ul style="list-style: none" id="stepbar">
		<li>
			<div class="akeeba-label--grey" id="step1">
				<?php echo AText::_('SETUP_LBL_FINALISATION_STEP_UPDATEHTACCESS')?>
			</div>
		</li>
	</ul>
</div>

<div id="finalisationInterface" style="display: none">
    <?php if ($this->autoprepend_disabled): ?>
    <p class="akeeba-block--warning">
        <?php echo AText::_('FINALISE_AUTOPREPEND_WARN')?>
    </p>
    <?php endif; ?>
	<?php if ($this->showconfig): ?>
	<?php echo $this->loadAnyTemplate('finalise/config'); ?>
	<?php else: ?>
	<div class="akeeba-panel--green">
		<header class="akeeba-block-header">
			<h3>
				<?php echo AText::_('FINALISE_LBL_READY'); ?>
			</h3>
		</header>
	<?php endif; ?>

    <?php if($this->replace_warnings):?>
    <div class="akeeba-block--warning">
        <h4><?php echo AText::_('FINALISE_REPLACEMENTS_WARN')?></h4>
        <div>
            <?php echo AText::sprintf('FINALISE_REPLACEMENTS_WARN_BODY', implode('<br/>', $this->replace_warnings))?>
        </div>
    </div>
    <?php endif; ?>

	<div id="finaliseKickstart" style="display: none">
		<p>
			<?php echo AText::_('FINALISE_LBL_KICKSTART'); ?>
		</p>
	</div>

	<div id="finaliseIntegrated" style="display: none">
		<p>
			<?php echo AText::_('FINALISE_LBL_INTEGRATED'); ?>
		</p>
	</div>

	<div id="finaliseStandalone" style="display: none">
		<p>
			<?php echo AText::_('FINALISE_LBL_STANDALONE'); ?>
		</p>
		<p>
			<button type="button" class="akeeba-btn--success--big" id="removeInstallation">
				<span class="akion-trash-b"></span>
				<?php echo AText::_('FINALISE_BTN_REMOVEINSTALLATION'); ?>
			</button>
		</p>
	</div>

	<div id="error-dialog" style="display: none">
		<div class="akeeba-renderer-fef">
			<div class="akeeba-panel--red">
				<header class="akeeba-block-header">
					<h3><?php echo AText::_('FINALISE_HEADER_ERROR') ?></h3>
				</header>
				<p><?php echo AText::_('FINALISE_LBL_ERROR') ?></p>
			</div>
		</div>
	</div>

	<div id="success-dialog" style="display: none">
		<div class="akeeba-renderer-fef">
			<div class="akeeba-panel--green">
				<header class="akeeba-block-header">
					<h3><?php echo AText::_('FINALISE_HEADER_SUCCESS') ?></h3>
				</header>
				<p>
					<?php echo AText::sprintf('FINALISE_LBL_SUCCESS', 'https://www.akeebabackup.com/documentation/troubleshooter/prbasicts.html') ?>
				</p>
				<a class="akeeba-btn--success" href="<?php echo AUri::base() . '../index.php' ?>">
					<span class="akion-arrow-right-c"></span>
					<?php echo AText::_('FINALISE_BTN_VISITFRONTEND'); ?>
				</a>
			</div>
		</div>
	</div>
</div>

