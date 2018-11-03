<?php
/**
 * @package   angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

$this->container->session->disableSave();
?>
	<form class="akeeba-form--stretch" action="index.php" method="post">
		<div class="akeeba-panel--teal">
			<h2 class="form-signin-heading">
				<?php echo AText::_('PASSWORD_HEADER_LOCKED'); ?>
			</h2>

			<div class="akeeba-form-group">
				<input type="password" name="password" id="password"
					   placeholder="<?php echo AText::_('PASSWORD_FIELD_PASSWORD_LABEL') ?>" />
			</div>

			<div class="akeeba-form-group">
				<button class="akeeba-btn--teal--big--block" type="submit">
					<span class="akion-lock-combination"></span>
					<?php echo AText::_('PASSWORD_BTN_UNLOCK') ?>
				</button>
			</div>
		</div>

		<div>
			<input type="hidden" name="view" value="password" />
			<input type="hidden" name="task" value="unlock" />
		</div>

	</form>
<?php
$script = <<<JS
akeeba.System.documentReady(function(){
	akeeba.System.triggerEvent(document.getElementById('password'), 'focus');
});

JS;

/** @var $this AView */

$document = $this->container->application->getDocument();

$x = $document->addScriptDeclaration($script);
