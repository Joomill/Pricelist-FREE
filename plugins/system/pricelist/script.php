<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;

class plgSystemPricelistInstallerScript
{
    public function install($parent)
    {
        jimport('joomla.filesystem.file');
        Factory::getDBO()->setQuery("UPDATE `#__extensions` SET `enabled` = 1 WHERE `type` = 'plugin' AND`element` = 'pricelist'")->execute();
    }
}