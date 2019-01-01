<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

/** @var $this AngieViewReplacedata */

$document = $this->container->application->getDocument();

$document->addScript('angie/js/json.js');
$document->addScript('angie/js/ajax.js');
$document->addScript('angie/js/finalise.js');

echo $this->loadAnyTemplate('steps/buttons');
echo $this->loadAnyTemplate('steps/steps');

$lblFrom   = AText::_('SETUP_LBL_REPLACEDATA_FROM');
$lblTo     = AText::_('SETUP_LBL_REPLACEDATA_TO');
$lblDelete = AText::_('SETUP_LBL_REPLACEDATA_DELETE');

$js = <<< JS

akeeba.System.documentReady(function($){
	replacements.strings["lblKey"] = "$lblFrom";
	replacements.strings["lblValue"] = "$lblTo";
	replacements.strings["lblDelete"] = "$lblDelete";
	replacements.showEditor();
});

JS;

$document->addScriptDeclaration($js);
?>


<div class="akeeba-block--info">
	<?php echo AText::_('SETUP_LBL_REPLACEDATA_INTRO'); ?>
</div>

<div id="replacementsGUI">
	<div class="akeeba-panel--teal">
		<header class="akeeba-block-header">
			<h3>
				<?php echo AText::_('SETUP_LBL_REPLACEDATA_REPLACEMENTS_HEAD'); ?>
			</h3>
		</header>

		<div id="textBoxEditor">
			<p>
				<?php echo AText::_('SETUP_LBL_REPLACEDATA_REPLACEMENTS_HELP'); ?>
			</p>

			<div class="akeeba-container--50-50">
				<div>
					<h4>
						<?php echo AText::_('SETUP_LBL_REPLACEDATA_FROM'); ?>
					</h4>
					<textarea rows="5" name="replaceFrom"
							  id="replaceFrom"><?php echo implode("\n", array_keys($this->replacements)); ?></textarea>
				</div>

				<div>
					<h4>
						<?php echo AText::_('SETUP_LBL_REPLACEDATA_TO'); ?>
					</h4>
					<textarea rows="5" name="replaceTo"
							  id="replaceTo"><?php echo implode("\n", $this->replacements); ?></textarea>
				</div>

				<div class="clearfix"></div>
			</div>
		</div>

		<div id="keyValueEditor" style="display: none">
			<p>
				<?php echo AText::_('SETUP_LBL_REPLACEDATA_REPLACEMENTS_JSGUI_HELP'); ?>
			</p>
			<div id="keyValueContainer">

			</div>
		</div>
	</div>

	<div class="akeeba-panel--info">
		<header class="akeeba-block-header">
			<h3>
				<?php echo AText::_('SETUP_LBL_REPLACEDATA_TABLES_HEAD'); ?>
			</h3>
		</header>
		<p>
			<?php echo AText::_('SETUP_LBL_REPLACEDATA_TABLES_HELP'); ?>
		</p>
		<div class="akeeba-container--66-33">
			<div class="AKEEBA_MASTER_FORM_STYLING">
				<select multiple size="10" id="extraTables">
					<?php if (!empty($this->otherTables))
					{
						foreach ($this->otherTables as $table): ?>
							<option value="<?php echo $this->escape($table) ?>" <?php echo (substr($table, 0, $this->prefixLen) == $this->prefix) ? 'selected="selected"' : '' ?>><?php echo $this->escape($table) ?></option>
						<?php endforeach;
					} ?>
				</select>
			</div>

			<div class="AKEEBA_MASTER_FORM_STYLING akeeba-form--horizontal">
				<div>
					<span id="showAdvanced" class="akeeba-btn--primary">
						<span class="akion-ios-gear"></span>
						<?php echo AText::_('SETUP_SHOW_ADVANCED') ?>
					</span>
				</div>
				<div id="replaceThrottle" style="display: none;">
					<h4><?php echo AText::_('SETUP_ADVANCE_OPTIONS') ?></h4>

					<!--
					<div class="akeeba-form-group">
						<label for="column_size"><?php echo AText::_('SETUP_REPLACE_DATA_COLUMNSIZE') ?></label>
						<input type="text" id="column_size" name="column_size" class="input-small" value="1048576" />
					</div>
					-->
					<div class="akeeba-form-group">
						<label for="batchSize"><?php echo AText::_('SETUP_REPLACE_DATA_BATCHSIZE') ?></label>
						<input type="text" id="batchSize" name="batchSize" class="input-small" value="100" />
					</div>
					<div class="akeeba-form-group">
						<label for="min_exec"><?php echo AText::_('SETUP_REPLACE_DATA_MIN_EXEC') ?></label>
						<input type="text" id="min_exec" name="min_exec" class="input-small" value="0" />
					</div>
					<div class="akeeba-form-group">
						<label for="max_exec"><?php echo AText::_('SETUP_REPLACE_DATA_MAX_EXEC') ?></label>
						<input type="text" id="max_exec" name="max_exec" class="input-small" value="3" />
					</div>
					<div class="akeeba-form-group">
						<label for="runtime_bias"><?php echo AText::_('SETUP_REPLACE_DATA_RUNTIME_BIAS') ?></label>
						<input type="text" id="runtime_bias" name="runtime_bias" class="input-small" value="75" />
					</div>
				</div>

				<div>
					<a href="index.php?view=replacedata&force=1" class="akeeba-btn--red--small">
						<span class="akion-fireball"></span>
						<?php echo AText::_('SETUP_LBL_REPLACEDATA_BTN_RESET'); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="replacementsProgress" class="akeeba-panel--info" style="display: none">
	<header class="akeeba-block-header">
		<h3>
			<?php echo AText::_('SETUP_LBL_REPLACEDATA_PROGRESS_HEAD'); ?>
		</h3>
	</header>
	<p>
		<?php echo AText::_('SETUP_LBL_REPLACEDATA_PROGRESS_HELP'); ?>
	</p>
	<pre id="replacementsProgressText"></pre>
	<div id="blinkenlights">
		<span class="akeeba-label--grey">&nbsp;&nbsp;&nbsp;</span>
		<span class="akeeba-label--teal">&nbsp;&nbsp;&nbsp;</span>
		<span class="akeeba-label--grey">&nbsp;&nbsp;&nbsp;</span>
		<span class="akeeba-label--teal">&nbsp;&nbsp;&nbsp;</span>
	</div>
</div>

<?php /* Replacement retry after error */ ?>
<div id="retry-panel" style="display: none">
	<div class="akeeba-panel--orange">
		<header class="akeeba-block-header">
			<h3 class="alert-heading">
				<?php echo AText::_('SETUP_REPLACE_HEADER_RETRY'); ?>
			</h3>
		</header>
		<div id="retryframe">
			<p><?php echo AText::_('SETUP_REPLACE_TEXT_FAILEDRETRY'); ?></p>
			<p>
				<strong>
					<?php echo AText::_('SETUP_REPLACE_TEXT_WILLRETRY'); ?>
					<span id="akeeba-retry-timeout">0</span>
					<?php echo AText::_('SETUP_REPLACE_TEXT_WILLRETRYSECONDS'); ?>
				</strong>
				<br />
				<button class="akeeba-btn--red--small" onclick="replacements.cancelResume(); return false;">
					<span class="akion-close-round"></span>
					<?php echo AText::_('SESSION_BTN_CANCEL'); ?>
				</button>
				<button class="akeeba-btn--green" onclick="replacements.resumeReplacement(); return false;">
					<span class="akion-ios-refresh-empty"></span>
					<?php echo AText::_('SETUP_REPLACE_TEXT_BTNRESUME'); ?>
				</button>
			</p>

			<p><?php echo AText::_('SETUP_REPLACE_TEXT_LASTERRORMESSAGEWAS'); ?></p>
			<p id="replacement-error-message-retry"></p>
		</div>
	</div>
</div>

<?php /* Replacement error (halt) */ ?>
<div id="error-panel" style="display: none">
	<div class="akeeba-panel--red">
		<header class="akeeba-block-header">
			<h3 class="alert-heading">
				<?php echo AText::_('SETUP_REPLACE_HEADER_REPLACEFAILED'); ?>
			</h3>
		</header>
		<div id="errorframe">
			<p>
				<?php echo AText::_('SETUP_REPLACE_TEXT_REPLACEFAILED'); ?>
			</p>
			<p id="replacement-error-message"></p>

			<div class="akeeba-block--info" id="error-panel-troubleshooting">
				<p>
					<?php echo AText::sprintf('SETUP_REPLACE_TEXT_RTFMTOSOLVE', 'https://www.akeebabackup.com/documentation/akeeba-solo/angie-wordpress-replace.html'); ?>
				</p>
				<p>
					<?php echo AText::sprintf('SETUP_REPLACE_TEXT_SOLVEISSUE_PRO', 'https://www.akeebabackup.com/support.html'); ?>
				</p>
			</div>

			<button class="akeeba-btn--dark--large"
					onclick="window.location='https://www.akeebabackup.com/documentation/akeeba-solo/angie-wordpress-replace.html'; return false;">
				<span class="akion-ios-book"></span>
				<?php echo AText::_('SETUP_REPLACE_TROUBLESHOOTINGDOCS'); ?>
			</button>
		</div>
	</div>
</div>

<?php /* Replacement warnings */ ?>
<div id="warning-panel" style="display:none">
	<div class="akeeba-panel--warning">
		<header class="akeeba-block-header">
			<h3 class="alert-heading">
				<?php echo AText::_('SETUP_REPLACE_HEADER_REPLACEWARNING'); ?>
			</h3>
		</header>

		<div id="warnings-list">
		</div>
	</div>
</div>