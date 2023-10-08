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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\ContentHelper;

HTMLHelper::_('behavior.core');

$layout = new FileLayout('style');
echo $layout->render([
    'wa' => $this->document->getWebAssetManager(),
    'params' => $this->params,
]);
?>

<div class="pricelist-categories <?php echo $this->params->get('pageclass_sfx'); ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <h1>
            <?php echo $this->params->get('page_heading'); ?>
        </h1>
    <?php endif; ?>

    <?php foreach ($this->items as $category) { ?>
    <div class="pricelist-category pricelist-category-<?php echo ($category->id)?>">

        <?php
        $layout = new FileLayout('category');
        echo $layout->render($category);
        ?>

    <div class="pricelist pricelist--collapse">
        <?php if ($this->params->get('show_table_heading'))  { ?>
            <div class="pricelist-row pricelist-row--head">

                <div class="pricelist-cell column-heading name-cell">
                    <?php echo Text::_($this->params->get('name_heading','COM_PRICELIST_PRODUCT_NAME')); ?>
                </div>

                <div class="pricelist-cell column-heading description-cell">
                    <?php echo Text::_($this->params->get('description_heading','COM_PRICELIST_PRODUCT_DESCRIPTION')); ?>
                </div>

                <div class="pricelist-cell column-heading price-cell">
                    <?php echo Text::_($this->params->get('price_heading','COM_PRICELIST_PRODUCT_PRICE')); ?>
                </div>

            </div>
        <?php } ?>

        <?php foreach($category->products as $i => $item) : ?>

            <div class="pricelist-row  pricelist-row-<?php echo $i % 2; ?> <?php echo($item->featured ? 'pricelist-featured' : ''); ?>">

                <div class="pricelist-cell name-cell">
                    <?php if ($this->params->get('show_mobile_heading')) {?>
                        <div class="pricelist-cell--heading"><?php echo Text::_($this->params->get('name_heading','COM_PRICELIST_PRODUCT_NAME')); ?></div>
                    <?php } ?>
                    <div class="pricelist-cell--content"><?php echo $item->name; ?></div>
                </div>

                <div class="pricelist-cell description-cell">
                    <?php if ($this->params->get('show_mobile_heading')) {?>
                        <div class="pricelist-cell--heading"><?php echo Text::_($this->params->get('description_heading','COM_PRICELIST_PRODUCT_DESCRIPTION')); ?></div>
                    <?php } ?>
                    <div class="pricelist-cell--content"><?php echo $item->description; ?></div>
                </div>

                <div class="pricelist-cell price-cell">
                    <?php if ($this->params->get('show_mobile_heading')) {?>
                        <div class="pricelist-cell--heading"><?php echo Text::_($this->params->get('price_heading','COM_PRICELIST_PRODUCT_PRICE')); ?></div>
                    <?php } ?>
                    <div class="pricelist-cell--content">
                        <?php if ($this->params->get('price_prefix')) {?>
                            <span class="pricelist-price-prefix">
                        <?php echo Text::_($this->params->get('price_prefix')); ?>
                    </span>
                        <?php } ?>
                        <?php echo $item->price; ?>
                        <?php if ($this->params->get('price_suffix')) {?>
                            <span class="pricelist-price-suffix">
                        <?php echo Text::_($this->params->get('price_suffix')); ?>
                    </span>
                        <?php } ?>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php } ?>
</div>
