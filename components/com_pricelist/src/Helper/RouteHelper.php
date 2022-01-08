<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Site\Helper;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Language\Multilanguage;

/**
 * Pricelist Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_pricelist
 * @since       4.0.0
 */
abstract class RouteHelper
{
    /**
     * Get the URL route for a foo from a foo ID, foos category ID and language
     *
     * @param integer $id The id of the foos
     * @param integer $catid The id of the foos's category
     * @param mixed $language The id of the language being used.
     * @return  string  The link to the foos
     * @since   4.0.0
     */
    public static function getProductsRoute($id, $catid, $language = 0)
    {
        // Create the link
        $link = 'index.php?option=com_pricelist&view=products&id=' . $id;

        if ($catid > 1)
        {
            $link .= '&catid=' . $catid;
        }

        if ($language && $language !== '*' && Multilanguage::isEnabled())
        {
            $link .= '&lang=' . $language;
        }

        return $link;
    }

    /**
     * Get the URL route for a foo from a foo ID, foos category ID and language
     *
     * @param integer $id The id of the foos
     * @param integer $catid The id of the foos's category
     * @param mixed $language The id of the language being used.
     * @return  string  The link to the foos
     * @since   4.0.0
     */
    public static function getProductRoute($id, $catid, $language = 0)
    {
        // Create the link
        $link = 'index.php?option=com_pricelist&view=product&id=' . $id;

        if ($catid > 1)
        {
            $link .= '&catid=' . $catid;
        }

        if ($language && $language !== '*' && Multilanguage::isEnabled())
        {
            $link .= '&lang=' . $language;
        }

        return $link;
    }

    /**
     * Get the URL route for a foos category from a foos category ID and language
     *
     * @param mixed $catid The id of the foos's category either an integer id or an instance of CategoryNode
     * @param mixed $language The id of the language being used.
     *
     * @return  string  The link to the foos
     *
     * @since   4.0.0
     */
    public static function getCategoryRoute($catid, $language = 0)
    {
        if ($catid instanceof CategoryNode)
        {
            $id = $catid->id;
        }
        else
        {
            $id = (int)$catid;
        }

        if ($id < 1)
        {
            $link = '';
        }
        else
        {
            // Create the link
            $link = 'index.php?option=com_pricelist&view=category&id=' . $id;

            if ($language && $language !== '*' && Multilanguage::isEnabled())
            {
                $link .= '&lang=' . $language;
            }
        }

        return $link;
    }

    /**
     * Get the URL route for a foos category from a foos category ID and language
     *
     * @param mixed $catid The id of the foos's category either an integer id or an instance of CategoryNode
     * @param mixed $language The id of the language being used.
     *
     * @return  string  The link to the foos
     *
     * @since   4.0.0
     */
    public static function getCategoriesRoute($catid, $language = 0)
    {
        if ($catid instanceof CategoryNode)
        {
            $id = $catid->id;
        }
        else
        {
            $id = (int)$catid;
        }

        if ($id < 1)
        {
            $link = '';
        }
        else
        {
            // Create the link
            $link = 'index.php?option=com_pricelist&view=category&id=' . $id;

            if ($language && $language !== '*' && Multilanguage::isEnabled())
            {
                $link .= '&lang=' . $language;
            }
        }

        return $link;
    }

    /**
     * Get the URL route for a foos category from a foos category ID and language
     *
     * @param mixed $catid The id of the foos's category either an integer id or an instance of CategoryNode
     * @param mixed $language The id of the language being used.
     *
     * @return  string  The link to the foos
     *
     * @since   4.0.0
     */
    public static function getMultipleRoute($language = 0)
    {
        $link = 'index.php?option=com_pricelist&view=multiple';

        if ($language && $language !== '*' && Multilanguage::isEnabled())
        {
            $link .= '&lang=' . $language;
        }

        return $link;
    }

    /**
     * Get the form route.
     *
     * @param integer $id The form ID.
     *
     * @return  string  The article route.
     *
     * @since   4.0.0
     */
    public static function getFormRoute($id)
    {
        return 'index.php?option=com_pricelist&task=product.edit&a_id=' . (int)$id;
    }
}
