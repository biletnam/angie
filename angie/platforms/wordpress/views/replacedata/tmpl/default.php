<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2017 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

/** @var $this AView */

$document = $this->container->application->getDocument();

$document->addScript('angie/js/json.js');
$document->addScript('angie/js/ajax.js');
$document->addScript('angie/js/finalise.js');

echo $this->loadAnyTemplate('steps/buttons');
echo $this->loadAnyTemplate('steps/steps');
?>

<div class="well well-small">
	<?php echo AText::_('SETUP_LBL_REPLACEDATA_INTRO'); ?>
</div>

<div id="replacementsGUI">
	<h3>
		<?php echo AText::_('SETUP_LBL_REPLACEDATA_REPLACEMENTS_HEAD'); ?>
	</h3>
	<p>
		<?php echo AText::_('SETUP_LBL_REPLACEDATA_REPLACEMENTS_HELP'); ?>
	</p>

	<div class="row-fluid">
		<div class="span6">
			<h4>
				<?php echo AText::_('SETUP_LBL_REPLACEDATA_FROM'); ?>
			</h4>
			<textarea class="span12" rows="5" name="replaceFrom" id="replaceFrom"><?php echo implode("\n", array_keys($this->replacements)); ?></textarea>
		</div>

		<div class="span6">
			<h4>
				<?php echo AText::_('SETUP_LBL_REPLACEDATA_TO'); ?>
			</h4>
			<textarea class="span12" rows="5" name="replaceTo" id="replaceTo"><?php echo implode("\n", $this->replacements); ?></textarea>
		</div>

		<div class="clearfix"></div>
	</div>

	<h3>
		<?php echo AText::_('SETUP_LBL_REPLACEDATA_TABLES_HEAD'); ?>
	</h3>
	<p>
		<?php echo AText::_('SETUP_LBL_REPLACEDATA_TABLES_HELP'); ?>
	</p>

	<div class="span4">
		<select multiple size="10" id="extraTables">
<?php if (!empty($this->otherTables)) foreach ($this->otherTables as $table): ?>
			<option value="<?php echo $this->escape($table) ?>" <?php echo (substr($table, 0, 3) == '#__') ? 'selected="selected"' : '' ?>><?php echo $this->escape($table) ?></option>
<?php endforeach; ?>
		</select>
	</div>

	<div class="span7 form-horizontal">
		<span id="showAdvanced" class="btn btn-primary"><?php echo AText::_('SETUP_SHOW_ADVANCED')?></span>
		<div id="replaceThrottle" style="display: none;">
			<h4><?php echo AText::_('SETUP_ADVANCE_OPTIONS')?></h4>
			<div class="control-group">
				<label class="control-label"><?php echo AText::_('SETUP_REPLACE_DATA_BATCHSIZE')?></label>
				<div class="controls">
					<input type="text" id="batchSize" name="batchSize" class="input-small" value="100" />
				</div>
			</div>
            <div class="control-group">
                <label class="control-label"><?php echo AText::_('SETUP_REPLACE_DATA_MIN_EXEC')?></label>
                <div class="controls">
                    <input type="text" id="min_exec" name="min_exec" class="input-small" value="0" />
                </div>
            </div>
			<div class="control-group">
				<label class="control-label"><?php echo AText::_('SETUP_REPLACE_DATA_MAX_EXEC')?></label>
				<div class="controls">
					<input type="text" id="max_exec" name="max_exec" class="input-small" value="3" />
				</div>
			</div>
            <div class="control-group">
                <label class="control-label"><?php echo AText::_('SETUP_REPLACE_DATA_RUNTIME_BIAS')?></label>
                <div class="controls">
                    <input type="text" id="runtime_bias" name="runtime_bias" class="input-small" value="75" />
                </div>
            </div>
		</div>

        <a href="index.php?view=replacedata&force=1" class="btn btn-danger btn-small">
            <span class="icon icon-white icon-fire"></span>
	        <?php echo AText::_('SETUP_LBL_REPLACEDATA_BTN_RESET'); ?>
        </a>
	</div>

	<div class="clearfix"></div>

	<div class="row-fluid">
	</div>
</div>

<div id="replacementsProgress" style="display: none">
	<h3>
		<?php echo AText::_('SETUP_LBL_REPLACEDATA_PROGRESS_HEAD'); ?>
	</h3>
	<p>
		<?php echo AText::_('SETUP_LBL_REPLACEDATA_PROGRESS_HELP'); ?>
	</p>
	<pre id="replacementsProgressText"></pre>
	<div id="blinkenlights">
		<span class="label label-default">&nbsp;&nbsp;&nbsp;</span><span class="label label-inverse">&nbsp;&nbsp;&nbsp;</span><span class="label label-default">&nbsp;&nbsp;&nbsp;</span><span class="label label-inverse">&nbsp;&nbsp;&nbsp;</span>
	</div>
</div>

<?php /* Backup retry after error */ ?>
<div id="retry-panel" style="display: none">
    <div class="alert alert-warning">
        <h3 class="alert-heading">
			<?php echo AText::_('SETUP_REPLACE_HEADER_RETRY'); ?>
        </h3>
        <div id="retryframe">
            <p><?php echo AText::_('SETUP_REPLACE_TEXT_FAILEDRETRY'); ?></p>
            <p>
                <strong>
					<?php echo AText::_('SETUP_REPLACE_TEXT_WILLRETRY'); ?>
                    <span id="akeeba-retry-timeout">0</span>
					<?php echo AText::_('SETUP_REPLACE_TEXT_WILLRETRYSECONDS'); ?>
                </strong>
                <br/>
                <button class="btn btn-danger btn-small" onclick="replacements.cancelResume(); return false;">
                    <span class="icon-cancel"></span>
					<?php echo AText::_('SESSION_BTN_CANCEL'); ?>
                </button>
                <button class="btn btn-success btn-small" onclick="replacements.resumeBackup(); return false;">
                    <span class="icon-redo"></span>
					<?php echo AText::_('SETUP_REPLACE_TEXT_BTNRESUME'); ?>
                </button>
            </p>

            <p><?php echo AText::_('SETUP_REPLACE_TEXT_LASTERRORMESSAGEWAS'); ?></p>
            <p id="backup-error-message-retry"></p>
        </div>
    </div>
</div>

<?php /* Backup error (halt) */ ?>
<div id="error-panel" style="display: none">
    <div class="alert alert-error">
        <h3 class="alert-heading">
			<?php echo AText::_('SETUP_REPLACE_HEADER_REPLACEFAILED'); ?>
        </h3>
        <div id="errorframe">
            <p>
				<?php echo AText::_('SETUP_REPLACE_TEXT_REPLACEFAILED'); ?>
            </p>
            <p id="backup-error-message"></p>

            <div class="alert alert-block alert-info" id="error-panel-troubleshooting">
                <p>
					<?php echo AText::sprintf('SETUP_REPLACE_TEXT_RTFMTOSOLVE', 'https://www.akeebabackup.com/documentation/troubleshooter/abbackup.html?utm_source=akeeba_backup&utm_campaign=backuperrorlink'); ?>
                </p>
                <p>
                    <?php echo AText::sprintf('SETUP_REPLACE_TEXT_SOLVEISSUE_PRO', 'https://www.akeebabackup.com/support.html?utm_source=akeeba_backup&utm_campaign=backuperrorpro'); ?>
                </p>
            </div>

            <button class="btn btn-large btn-primary" onclick="window.location='https://www.akeebabackup.com/documentation/troubleshooter/abbackup.html?utm_source=akeeba_backup&utm_campaign=backuperrorbutton'; return false;">
                <span class="icon-book icon-white"></span>
				<?php echo AText::_('SETUP_REPLACE_TROUBLESHOOTINGDOCS'); ?>
            </button>
        </div>
    </div>
</div>