<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_PRICELIST',
    'formURL' => 'index.php?option=com_pricelist&view=products',
    'helpURL' => 'https://www.joomill-extensions.com/extensions/price-list-component/documentation',
    'icon' => 'icon-copy article',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_pricelist') || count($user->getAuthorisedCategories('com_pricelist', 'core.create')) > 0)
{
    $displayData['createURL'] = 'index.php?option=com_pricelist&task=product.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
