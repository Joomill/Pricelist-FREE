<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2023. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

$fields  = $displayData["fields"];
$category  = $displayData["category"];
$display  = $displayData["display"];

?>

<?php foreach ($fields as $field) {
    if ($field->params["display"] == $display) {
        $width = $field->params->get('width');
        $align = $field->params->get('align');
        $assignedcategories = FieldsHelper::getAssignedCategoriesIds($field->id);
        if ((!$assignedcategories) || (in_array($category->id, $assignedcategories, true))) { ?>
            <div class="pricelist-cell column-heading field-<?php echo $field->id;?>-cell"
                 style="<?php if (!empty($width)) { ?>width: <?php echo $width;?>; <?php } ?><?php if (!empty($align)) { ?>text-align: <?php echo $align;?>; <?php } ?>"><?php echo $field->label;?>
            </div>
        <?php }
    }
}
?>