<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2023. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Site\Helper;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\Component\Categories\Administrator\Helper\CategoryAssociationHelper;

/**
 * Pricelist Component Association Helper
 *
 * @since  3.0
 */
abstract class AssociationHelper extends CategoryAssociationHelper
{
    /**
     * Method to get the associations for a given item
     *
     * @param integer $id Id of the item
     * @param string $view Name of the view
     *
     * @return  array   Array of associations for the item
     *
     * @since  3.0
     */
    public static function getAssociations($id = 0, $view = null)
    {
        $jinput = Factory::getApplication()->input;
        $view = $view ?? $jinput->get('view');
        $id = empty($id) ? $jinput->getInt('id') : $id;

        if ($view === 'products')
        {
            if ($id)
            {
                $associations = Associations::getAssociations('com_pricelist', '#__pricelist_products', 'com_pricelist.item', $id);

                $return = array();

                foreach ($associations as $tag => $item)
                {
                    $return[$tag] = RouteHelper::getProductRoute($item->id, (int)$item->catid, $item->language);
                }

                return $return;
            }
        }

        if ($view === 'category' || $view === 'categories')
        {
            return self::getCategoryAssociations($id, 'com_pricelist');
        }

        return array();

    }
}
