<?php
/**
 * @package   angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

foreach ([
	         'error'   => 'failure',
	         'warning' => 'warning',
	         'success' => 'success',
	         'info'    => 'info',
         ] as $type => $class):
	$messages = AApplication::getInstance()->getMessageQueueFor($type);
	if (!empty($messages)):
		$class = "alert-$class";
		?>
        <div id="akeeba-backup-message-<?php echo $type?>" class="akeeba-backup-message akeeba-block--<?php echo $class ?> small">
			<?php foreach ($messages as $message): ?>
                <p><?php echo $message ?></p>
			<?php endforeach; ?>
        </div>
	<?php
	endif;
endforeach;
