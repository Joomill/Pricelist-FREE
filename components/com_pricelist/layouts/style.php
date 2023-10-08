<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2023. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */

$wa = $displayData["wa"];
$params  = $displayData["params"];

$wa->getRegistry()->addExtensionRegistryFile('com_pricelist');
$wa->useStyle('com_pricelist.pricelist-default');
$wa->addInlineStyle('.name-cell {
        flex-basis: '.$params->get('name_width','300px').';
        font-size:  '.$params->get('name_fontsize','1em').';
        font-weight:  '.$params->get('name_fontweight','normal').';
        text-align:  '.$params->get('name_textalign','left').';
    }');
$wa->addInlineStyle('.description-cell {
        font-size:  '.$params->get('description_fontsize','1em').';
        font-weight:  '.$params->get('description_fontweight','normal').';
        text-align:  '.$params->get('description_textalign','left').';
    }');
$wa->addInlineStyle('.price-cell {
        flex-basis: '.$params->get('price_width','100px').';
        font-size:  '.$params->get('price_fontsize','1em').';
        font-weight:  '.$params->get('price_fontweight','normal').';
        text-align:  '.$params->get('price_textalign','right').';
    }');
$wa->addInlineStyle('.pricelist-featured {
        background:  '.$params->get('featured_bgcolor','#ffffff').';
    }');
$wa->addInlineStyle('.pricelist-featured .pricelist-cell { 
        color:  '.$params->get('featured_fontcolor','#333333').';
        font-size:  '.$params->get('featured_fontsize','1em').';
        font-weight:  '.$params->get('featured_fontweight','normal').';
    }');
if ($params->get('show_table_heading')) {
    $wa->addInlineStyle('.column-heading {
        background-color: ' . $params->get('heading_bgcolor', '#39b7b6') . ';
        color:  ' . $params->get('heading_fontcolor', '#ffffff') . ';
        font-size:  ' . $params->get('heading_fontsize', '1em') . ';
        font-weight:  ' . $params->get('heading_fontweight', 'normal') . ';
    }');
}
if ($params->get('custom_css')) {
    $wa->addInlineStyle('' . $params->get('custom_css') . '');
}
