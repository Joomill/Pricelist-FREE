<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Administrator\Controller;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Pricelist master display controller.
 *
 * @since   4.0.0
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $default_view = 'products';

    /**
     * Method to display a view.
     *
     * @param   boolean     $cachable   If true, the view output will be cached
     * @param   array       $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return  BaseController|bool     This object to support chaining.
     *
     * @throws  \Exception
     * @since   4.0.0
     *
     */
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display();
    }
}