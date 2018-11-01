<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/** @var  AngieViewMain  $this */
?>
<div class="akeeba-panel--info">
    <header class="akeeba-block-header">
        <h3><?php echo AText::_('MAIN_HEADER_SITEINFO') ?></h3>
    </header>
    <p><?php echo AText::_('MAIN_LBL_SITEINFO') ?></p>
    <table class="akeeba-table--striped" width="100%">
        <tbody>
        <tr>
            <td>
                <label><?php echo AText::_('MAIN_LBL_SITE_JOOMLA') ?></label>
            </td>
            <td><?php echo property_exists($this, 'joomlaVersion') ? $this->joomlaVersion : $this->version ?></td>
        </tr>
        <tr>
            <td>
                <label><?php echo AText::_('MAIN_LBL_SITE_PHP') ?></label>
            </td>
            <td><?php echo PHP_VERSION ?></td>
        </tr>
        </tbody>
    </table>
</div>

