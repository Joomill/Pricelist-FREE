<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2023. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Button\FeaturedButton;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$user      = Factory::getUser();
$userId    = $user->get('id');
$assoc = Associations::isEnabled();
$canChange = true;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$params = ComponentHelper::getParams('com_pricelist');

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

if ($saveOrder && !empty($this->items))
{
    $saveOrderingUrl = 'index.php?option=com_pricelist&task=products.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>

<form action="<?php echo Route::_('index.php?option=com_pricelist'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                // Search tools bar
                echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
                ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-warning">
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="ProductList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_PRICELIST_PRODUCTS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                        <tr>
                            <td scope="col" class="w-1 text-center">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </td>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-20">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-50 d-none d-md-table-cell">
                                <?php echo Text::_('COM_PRICELIST_PRODUCT_DESCRIPTION'); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell">
                                <?php echo Text::_('COM_PRICELIST_PRODUCT_PRICE'); ?>
                            </th>
                            <th scope="col" class="w-3 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody<?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
                        <?php
                        $n = count($this->items);
                        foreach ($this->items as $i => $item) :
                            $canCreate  = $user->authorise('core.create',     'com_pricelist.category.' . $item->catid);
                            $canEdit    = $user->authorise('core.edit',       'com_pricelist.category.' . $item->catid);
                            $canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || is_null($item->checked_out);
                            $canEditOwn = $user->authorise('core.edit.own',   'com_pricelist.category.' . $item->catid) && $item->created_by == $userId;
                            $canChange  = $user->authorise('core.edit.state', 'com_pricelist.category.' . $item->catid) && $canCheckin;
                            ?>
                            <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->catid; ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php
                                    $iconClass = '';
                                    if (!$canChange)
                                    {
                                        $iconClass = ' inactive';
                                    }
                                    elseif (!$saveOrder)
                                    {
                                        $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
                                    }
                                    ?>
                                    <span class="sortable-handler<?php echo $iconClass ?>">
										<span class="icon-ellipsis-v" aria-hidden="true"></span>
									</span>
                                    <?php if ($canChange && $saveOrder) : ?>
                                        <input type="text" name="order[]" size="5"
                                               value="<?php echo $item->ordering; ?>"
                                               class="width-20 text-area-order hidden">
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $options = [
                                        'task_prefix' => 'products.',
                                        'disabled' => !$canChange,
                                        'id' => 'state-' . $item->id
                                    ];

                                    echo (new PublishedButton)->render((int)$item->published, $i, $options, $item->publish_up, $item->publish_down);
                                    ?>

                                </td>
                                <th scope="row" class="has-context">
                                    <?php if ($item->checked_out) : ?>
                                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'products.', true); ?>
                                    <?php endif; ?>
                                    <?php $editIcon = '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
                                    <?php if ($canEdit || $canEditOwn) : ?>
                                        <a href="<?php echo Route::_('index.php?option=com_pricelist&task=product.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
                                            <?php echo $this->escape($item->name); ?></a>
                                    <?php else : ?>
                                        <?php echo $this->escape($item->name); ?>
                                    <?php endif; ?>
                                    <div class="small">
                                        <?php echo Text::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
                                    </div>
                                </th>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->description; ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php if ($params->get('price_prefix')) { ?>
                                            <?php echo Text::_($params->get('price_prefix')); ?>
                                    <?php } ?>
                                    <?php echo $item->price; ?>
                                    <?php if ($params->get('price_suffix')) { ?>
                                        <?php echo Text::_($params->get('price_suffix')); ?>
                                    <?php } ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->id; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php echo $this->pagination->getListFooter(); ?>

                    <?php echo HTMLHelper::_(
                        'bootstrap.renderModal',
                        'collapseModal',
                        [
                            'title' => Text::_('COM_PRICELIST_BATCH_OPTIONS'),
                            'footer' => $this->loadTemplate('batch_footer'),
                        ],
                        $this->loadTemplate('batch_body')
                    ); ?>


                <?php endif; ?>
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>

<div class="alert alert-warning text-center">
    <?php echo Text::_('COM_PRICELIST_FREE_VERSION'); ?><br/>
    <a href="https://www.joomill-extensions.com/extensions/price-list-component" class="btn btn-primary" target="_blank"><?php echo Text::_('COM_PRICELIST_GO_PRO'); ?></a>
</div>