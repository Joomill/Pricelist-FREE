<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Site\Controller;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * Controller for single product view
 *
 * @since  4.0.0
 */
class ProductController extends FormController
{
    /**
     * The URL view item variable.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $view_item = 'form';

    /**
     * Method to get a model object, loading it if required.
     *
     * @param string $name The model name. Optional.
     * @param string $prefix The class prefix. Optional.
     * @param array $config Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     *
     * @since   4.0.0
     */
    public function getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, array('ignore_request' => false));
    }

    /**
     * Method override to check if you can add a new record.
     *
     * @param array $data An array of input data.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function allowAdd($data = array())
    {
        if ($categoryId = ArrayHelper::getValue($data, 'catid', $this->input->getInt('catid'), 'int'))
        {
            $user = $this->app->getIdentity();

            // If the category has been passed in the data or URL check it.
            return $user->authorise('core.create', 'com_pricelist.category.' . $categoryId);
        }

        // In the absence of better information, revert to the component permissions.
        return parent::allowAdd();
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param array $data An array of input data.
     * @param string $key The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $recordId = (int)isset($data[$key]) ? $data[$key] : 0;

        if (!$recordId)
        {
            return false;
        }

        // Need to do a lookup from the model.
        $record = $this->getModel()->getItem($recordId);
        $categoryId = (int)$record->catid;

        if ($categoryId)
        {
            $user = $this->app->getIdentity();

            // The category has been set. Check the category permissions.
            if ($user->authorise('core.edit', $this->option . '.category.' . $categoryId))
            {
                return true;
            }

            // Fallback on edit.own.
            if ($user->authorise('core.edit.own', $this->option . '.category.' . $categoryId))
            {
                return ($record->created_by == $user->id);
            }

            return false;
        }

        // Since there is no asset tracking, revert to the component permissions.
        return parent::allowEdit($data, $key);
    }

    /**
     * Method to cancel an edit.
     *
     * @param string $key The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     *
     * @since   4.0.0
     */
    public function cancel($key = null)
    {
        $result = parent::cancel($key);

        $this->setRedirect(Route::_($this->getReturnPage(), false));

        return $result;
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param integer $recordId The primary key id for the item.
     * @param string $urlVar The name of the URL variable for the id.
     *
     * @return  string    The arguments to append to the redirect URL.
     *
     * @since   4.0.0
     */
    protected function getRedirectToItemAppend($recordId = 0, $urlVar = 'id')
    {
        // Need to override the parent method completely.
        $tmpl = $this->input->get('tmpl');

        $append = '';

        // Setup redirect info.
        if ($tmpl)
        {
            $append .= '&tmpl=' . $tmpl;
        }

        $append .= '&layout=edit';

        $append .= '&' . $urlVar . '=' . (int)$recordId;

        $itemId = $this->input->getInt('Itemid');
        $return = $this->getReturnPage();
        $catId = $this->input->getInt('catid');

        if ($itemId)
        {
            $append .= '&Itemid=' . $itemId;
        }

        if ($catId)
        {
            $append .= '&catid=' . $catId;
        }

        if ($return)
        {
            $append .= '&return=' . base64_encode($return);
        }

        return $append;
    }

    /**
     * Method to save a record.
     *
     * @param string $key The name of the primary key of the URL variable.
     * @param string $urlVar The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if successful, false otherwise.
     *
     * @since   4.0.0
     */
    public function save($key = null, $urlVar = null)
    {
        $result = parent::save($key, $urlVar = null);
        $this->setRedirect(Route::_($this->getReturnPage(), false));

        return $result;
    }

    /**
     * Get the return URL.
     *
     * If a "return" variable has been passed in the request
     *
     * @return  string    The return URL.
     *
     * @since   4.0.0
     */
    protected function getReturnPage()
    {
        $return = $this->input->get('return', null, 'base64');

        if (empty($return) || !Uri::isInternal(base64_decode($return)))
        {
            return Uri::base();
        }

        return base64_decode($return);
    }
}