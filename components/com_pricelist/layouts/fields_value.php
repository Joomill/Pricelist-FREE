<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

$fields  = $displayData["fields"];
$category  = $displayData["category"];
$display  = $displayData["display"];
$params  = $displayData["params"];
?>

<?php foreach ($fields as $field) {
    if ($field->params["display"] == $display) {
        $width = $field->params->get('width');
        $align = $field->params->get('align');
        ?>
        <div class="pricelist-cell field-<?php echo $field->id;?>-cell" style="<?php if (!empty($width)) { ?>width:<?php echo $width;?>;<?php } ?><?php if (!empty($align)) { ?>text-align:<?php echo $align;?>;<?php } ?>">
        <?php if ($params->get('show_mobile_heading')) {?>
                <div class="pricelist-cell--heading"><?php echo $field->label;?></div>
        <?php } ?>
            <div class="pricelist-cell--content <?php echo $field->params->value_render_class ;?>">
                <?php echo $field->params->get('prefix');?>
                <?php echo $field->value;?>
                <?php echo $field->params->get('suffix');?>
            </div>
        </div>
        <?php
    }
}
?>