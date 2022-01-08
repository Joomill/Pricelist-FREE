<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

$category = $displayData;
$category->params = json_decode($category->params);
?>

    <h2 class="category-heading"><?php echo $category->title; ?></h2>

    <?php if ($category->params->image) { ?>
        <?php $alt = empty($category->params->image_alt) && empty($category->params->image_alt_empty) ? '' : 'alt="' . htmlspecialchars($category->params->image_alt, ENT_COMPAT, 'UTF-8') . '"'; ?>
        <div class="category-image">
            <img src="<?php echo $category->params->image; ?>" <?php echo $alt; ?>>
        </div>
    <?php } ?>

    <div class="category-description"><?php echo $category->description; ?></div>
