<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * This models supports retrieving lists of articles.
 *
 * @since  1.6
 */
class MultipleModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     \JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id', 'c.id',
                'title', 'c.title',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     * @return  void
     * @since   4.0.0
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_pricelist');

        // Get list ordering default from the parameters
        if ($menu = $app->getMenu()->getActive())
        {
            $menuParams = $menu->getParams();
        }
        else
        {
            $menuParams = new Registry;
        }

        $mergedParams = clone $params;
        $mergedParams->merge($menuParams);


        // Optional filter text
        $itemid = $app->input->get('Itemid', 0, 'int');
        $search = $app->getUserStateFromRequest('com_pricelist.category.list.' . $itemid . '.filter-search', 'filter-search', '', 'string');
        $this->setState('list.filter', $search);

        $orderCol = $app->input->get('filter_order', $mergedParams->get('initial_sort', 'ordering'));

        if (!in_array($orderCol, $this->filter_fields))
        {
            $orderCol = 'lft';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $app->input->get('filter_order_Dir', 'ASC');

        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
        {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        $id = $app->input->get('id', 0, 'int');
        $this->setState('category.id', $id);


        $user = $app->getIdentity();

        if ((!$user->authorise('core.edit.state', 'com_pricelist')) && (!$user->authorise('core.edit', 'com_pricelist')))
        {
            // Limit to published for people who can't edit or edit.state.
            $this->setState('filter.published', 1);

            // Filter by start and end dates.
            $this->setState('filter.publish_date', true);
        }

        $this->setState('filter.language', Multilanguage::isEnabled());

        // Load the parameters.
        $this->setState('params', $params);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select', 'c.*'
            )
        );

        $query->from('`#__categories` AS a');


        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=c.checked_out');

        // Join over the created by field 'created_by'
        $query->select('created_by.name AS created_by');
        $query->join('LEFT', '#__users AS created_by ON created_by.id = c.created_by');


        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('c.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');

            }
        }

        //Filtering access
        $filter_access = $this->state->get("filter.access");
        if ($filter_access) {
            $query->where("c.access = '".$filter_access."'");
        }

        //Filtering language
        $filter_language = $this->state->get("filter.language");
        if ($filter_language) {
            $query->where("c.language = '".$filter_language."'");
        }

        //Filtering created_by
        $filter_created_by = $this->state->get("filter.created_by");
        if ($filter_created_by) {
            $query->where("c.created_by = '".$filter_created_by."'");
        }

        return $query;
    }

    public function getItems() {
        return parent::getItems();
    }

    public function getCategory() {
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $this->params       = $app->getParams('com_pricelist');

        $query->select($this->getState('list.select', 'c.*'))
            ->from($db->quoteName('#__categories', 'c'))
            ->where($db->quoteName('c.extension') . ' = "com_pricelist"')
            ->whereIn($db->quoteName('c.id'), $this->params['catid'])
            ->whereIn($db->quoteName('c.access'), $groups);
            //->order($db->escape($this->getState('list.direction', 'ASC')));

        // Filter by state
        $state = $this->getState('filter.published');

        if (is_numeric($state))
        {
            $query->where($db->quoteName('c.published') . ' = :published');
            $query->bind(':published', $state, ParameterType::INTEGER);
        }
        else
        {
            $query->whereIn($db->quoteName('c.published'), [0, 1, 2]);
        }

        // Filter by language
        if ($this->getState('filter.language'))
        {
            $query->whereIn($db->quoteName('c.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        // Add the list ordering clause.
        $this->setState('list.ordering', 'lft');
        $query->order(
            $db->escape($this->getState('list.ordering', 'c.lft')) . ' ' . $db->escape($this->getState('list.direction', 'ASC'))
        );

        $db->setQuery($query);
        $items = $db->loadObjectList();

        foreach($items as $key=>$category){
            // Create a new query object.
            $db = $this->getDbo();
            $product_query = $db->getQuery(true);

            $product_query->select($this->getState('list.select', 'p.*'))
                        ->from($db->quoteName('#__pricelist_products', 'p'))
                        ->where($db->quoteName('p.catid') . ' = ' . $category->id)
                        ->whereIn($db->quoteName('p.access'), $groups);
                        //->order($db->escape($this->getState('list.direction', 'ASC')));

            // Filter by state
            $state = $this->getState('filter.published');

            if (is_numeric($state))
            {
                $product_query->where($db->quoteName('p.published') . ' = :published');
                $product_query->bind(':published', $state, ParameterType::INTEGER);
            }
            else
            {
                $product_query->whereIn($db->quoteName('p.published'), [0, 1, 2]);
            }

            // Filter by start and end dates.
            $nowDate = Factory::getDate()->toSql();

            if ($this->getState('filter.publish_date'))
            {
                $product_query->where('(' . $db->quoteName('p.publish_up')
                    . ' IS NULL OR ' . $db->quoteName('p.publish_up') . ' <= :publish_up)'
                )
                    ->where('(' . $db->quoteName('p.publish_down')
                        . ' IS NULL OR ' . $db->quoteName('p.publish_down') . ' >= :publish_down)'
                    )
                    ->bind(':publish_up', $nowDate)
                    ->bind(':publish_down', $nowDate);
            }

            // Filter by language
            if ($this->getState('filter.language'))
            {
                $product_query->whereIn($db->quoteName('p.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
            }

            // Add the list ordering clause
            $this->setState('list.ordering', 'ordering');
            $product_query->order(
               $db->escape($this->getState('list.ordering', 'p.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC'))
           );

            $db->setQuery($product_query);
            $products = $db->loadObjectList();

            $items[$key]->products = $products;
        }

        return $items;
    }
}