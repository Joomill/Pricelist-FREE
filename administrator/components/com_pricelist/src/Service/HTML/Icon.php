<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Administrator\Service\HTML;

// No direct access.
defined('_JEXEC') or die;

use Joomill\Component\Pricelist\Site\Helper\RouteHelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Pricelist Component HTML Helper
 *
 * @since  4.0.0
 */
class Icon
{
    /**
     * The application
     *
     * @var    CMSApplication
     *
     * @since  4.0.0
     */
    private $application;

    /**
     * Service constructor
     *
     * @param CMSApplication $application The application
     *
     * @since   4.0.0
     */
    public function __construct(CMSApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Method to generate a link to the create item page for the given category
     *
     * @param object $category The category information
     * @param Registry $params The item parameters
     * @param array $attribs Optional attributes for the link
     *
     * @return  string  The HTML markup for the create item link
     *
     * @since  4.0.0
     */
    public static function create($category, $params, $attribs = array())
    {
        $uri = Uri::getInstance();

        $url = 'index.php?option=com_pricelist&task=product.add&return=' . base64_encode($uri) . '&id=0&catid=' . $category->id;

        $text = '';

        if ($params->get('show_icons'))
        {
            $text .= '<span class="icon-plus icon-fw" aria-hidden="true"></span>';
        }

        $text .= Text::_('COM_PRICELIST_ADD_PRODUCT');

        // Add the button classes to the attribs array
        if (isset($attribs['class']))
        {
            $attribs['class'] .= ' btn btn-primary';
        }
        else
        {
            $attribs['class'] = 'btn btn-primary';
        }

        $button = HTMLHelper::_('link', Route::_($url), $text, $attribs);

        return $button;
    }

    /**
     * Display an edit icon for the product.
     *
     * This icon will not display in a popup window, nor if the product is trashed.
     * Edit access checks must be performed in the calling code.
     *
     * @param object $product The product information
     * @param Registry $params The item parameters
     * @param array $attribs Optional attributes for the link
     * @param boolean $legacy True to use legacy images, false to use icomoon based graphic
     *
     * @return  string   The HTML for the product edit icon.
     *
     * @since   4.0.0
     */
    public static function edit($product, $params, $attribs = array(), $legacy = false)
    {
        $user = Factory::getUser();
        $uri = Uri::getInstance();

        // Ignore if the state is negative (trashed).
        if ($product->published < 0)
        {
            return '';
        }

        // Show checked_out icon if the product is checked out by a different user
        if (($product->checked_out) && $product->checked_out !== $user->get('id'))
        {
            $icon = 'lock';
            $text = '<span class="hasTooltip icon-' . $icon . '" aria-hidden="true"></span>';
            //$text .= Text::_('JLIB_HTML_CHECKED_OUT');

            $output = HTMLHelper::_('link', '#', $text);

            return $output;
        }

        $url = 'index.php?option=com_pricelist&task=product.edit&id=' . (int)$product->id . '&return=' . base64_encode($uri);

        $nowDate = strtotime(Factory::getDate());
        $icon = 'edit';

        if (($product->publish_up !== null && strtotime($product->publish_up) > $nowDate)
            || ($product->publish_down !== null && strtotime($product->publish_down) < $nowDate
                && $product->publish_down !== Factory::getDbo()->getNullDate()))
        {
            $icon = 'eye-slash';
        }

        $aria_described = 'editproduct-' . (int)$product->id;

        $text = '<span class="icon-' . $icon . '" aria-hidden="true"></span>';
        //$text .= Text::_('JGLOBAL_EDIT');

        $attribs['aria-describedby'] = $aria_described;
        $output = HTMLHelper::_('link', Route::_($url), $text, $attribs);

        return $output;
    }
}
