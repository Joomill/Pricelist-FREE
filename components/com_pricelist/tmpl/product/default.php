<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;

$tparams = $this->item->params;
$canDo = ContentHelper::getActions('com_pricelist', 'category', $this->item->catid);
$canEdit = $canDo->get('core.edit');

echo $this->item->name;


?>

<?php if ($canEdit) : ?>
    <div class="icons">
        <div class="float-end">
            <div>
                <?php echo HTMLHelper::_('pricelisticon.edit', $this->item, $tparams); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
echo $this->item->event->afterDisplayTitle;
echo $this->item->event->beforeDisplayContent;
echo $this->item->event->afterDisplayContent;