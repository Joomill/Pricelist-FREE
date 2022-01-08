<?php

/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Product model for the Joomla Pricelist component.
 *
 * @since  4.0.0
 */
class ProductModel extends BaseDatabaseModel
{
    /**
     * @var string item
     */
    protected $_item = null;

    /**
     * Gets a product
     *
     * @param integer $pk Id for the product
     *
     * @return  mixed Object or null
     *
     * @since  4.0.0
     */
    public function getItem($pk = null)
    {
        $app = Factory::getApplication();
        $pk = $app->input->getInt('id');

        if ($this->_item === null)
        {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk]))
        {
            try
            {
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select('*')
                    ->from($db->quoteName('#__pricelist_products', 'a'));
                //->where('a.id = ' . (int) $pk);

                $db->setQuery($query);
                $data = $db->loadObject();

                if (empty($data))
                {
                    throw new \Exception(Text::_('COM_PRICELIST_ERROR_PRODUCT_NOT_FOUND'), 404);
                }

                $this->_item[$pk] = $data;
            }
            catch (\Exception $e)
            {
                $this->setError($e);
                $this->_item[$pk] = false;
            }
        }

        return $this->_item[$pk];
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        $this->setState('product.id', $app->input->getInt('id'));
        $this->setState('params', $app->getParams());
    }
}