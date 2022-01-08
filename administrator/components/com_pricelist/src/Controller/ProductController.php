<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Administrator\Controller;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Controller for a single product
 *
 * @since  4.0.0
 */
class ProductController extends FormController
{
    /**
     * Method to run batch operations.
     *
     * @param   object $model The model.
     *
     * @return  boolean   True if successful, false otherwise and internal error is set.
     *
     * @since   4.0.0
     */
    public function batch($model = null)
    {
        $this->checkToken();

        $model = $this->getModel('Product', 'Administrator', array());

        // Preset the redirect
        $this->setRedirect(Route::_('index.php?option=com_pricelist&view=products' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }
}
