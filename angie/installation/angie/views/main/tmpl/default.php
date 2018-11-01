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

if (file_exists(APATH_INSTALLATION . '/platform/js/main.js'))
{
	$document->addScript('platform/js/main.js');
}
else
{
	$document->addScript('angie/js/main.js');
}

$url = 'index.php';

$document->addScriptDeclaration(<<<JS

var akeebaAjax = null;

akeeba.System.documentReady(function ()
{
	akeebaAjax = new akeebaAjaxConnector('$url');
});
JS

);

$document->appendButton(
	'GENERIC_BTN_STARTOVER', 'index.php?view=main&task=startover', 'red', 'fireball'
);
$document->appendButton(
	'GENERIC_BTN_RECHECK', 'javascript:mainGetPage();', 'orange', 'loop'
);

echo $this->loadAnyTemplate('steps/buttons');
?>
<noscript>
<div class="alert alert-error">
	<h3><?php echo AText::_('MAIN_HEADER_NOJAVASCRIPT') ?></h3>
	<p><?php echo AText::_('MAIN_LBL_NOJAVASCRIPT') ?></p>
</div>
</noscript>
<div class="well" style="text-align: center;">
	<h1><?php echo AText::_('MAIN_HEADER_INITIALISING') ?></h1>
	<p>
		<img src="template/angie/img/loading_big.gif" />
	</p>
	<p>
		<?php echo AText::_('MAIN_LBL_INITIALISINGWAIT') ?>
	</p>
</div>
