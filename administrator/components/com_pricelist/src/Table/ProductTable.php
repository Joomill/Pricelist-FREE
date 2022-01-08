<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Administrator\Table;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Product Table class.
 *
 * @since  4.0.0
 */
class ProductTable extends Table implements TaggableTableInterface, VersionableTableInterface
{
    use TaggableTableTrait;

    /**
     * Constructor
     *
     * @param DatabaseDriver $db Database connector object
     *
     * @since   4.0.0
     */
    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_pricelist.product';

        parent::__construct('#__pricelist_products', 'id', $db);
    }

    /**
     * Get the type alias
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }

    /**
     * Stores a product.
     *
     * @param boolean $updateNulls True to update fields even if they are null.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   4.0.0
     */
    public function store($updateNulls = true)
    {
        $date   = Factory::getDate()->toSql();
        $userId = Factory::getUser()->id;

        // Set created date if not set.
        if (!(int) $this->created)
        {
            $this->created = $date;
        }

        if ($this->id)
        {
            // Existing item
            $this->modified_by = $userId;
            $this->modified    = $date;
        }
        else
        {
            // Field created_by field can be set by the user, so we don't touch it if it's set.
            if (empty($this->created_by))
            {
                $this->created_by = $userId;
            }

            if (!(int) $this->modified)
            {
                $this->modified = $date;
            }

            if (empty($this->modified_by))
            {
                $this->modified_by = $userId;
            }
        }

        // Transform the params field
        if (is_array($this->params))
        {
            $registry = new Registry($this->params);
            $this->params = (string)$registry;
        }

        // Verify that the alias is unique
        $table = Table::getInstance('ProductTable', __NAMESPACE__ . '\\', array('dbo' => $this->getDbo()));

        if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0))
        {
            $this->setError(Text::_('COM_PRICELIST_ERROR_UNIQUE_ALIAS'));

            return false;
        }

        return parent::store($updateNulls);
    }

    /**
     * Generate a valid alias from title / date.
     * Remains public to be able to check for duplicated alias before saving
     *
     * @return  string
     */

    public function generateAlias()
    {
        if (empty($this->alias))
        {
            $this->alias = $this->name;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

        if (trim(str_replace('-', '', $this->alias)) == '')
        {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return $this->alias;
    }

    /**
     * Overloaded check function
     *
     * @return  boolean
     *
     * @see     Table::check
     * @since   4.0.0
     */
    public function check()
    {
        try
        {
            parent::check();
        }
        catch (\Exception $e)
        {
            $this->setError($e->getMessage());

            return false;
        }

        // Check for valid name
        if (trim($this->name) == '')
        {
            $this->setError(Text::_('COM_PRICELIST_WARNING_PROVIDE_VALID_NAME'));
            return false;
        }

        // Generate a valid alias
        $this->generateAlias();

        // Check the publish down date is not earlier than publish up.
        if ((int)$this->publish_down > 0 && $this->publish_down < $this->publish_up)
        {
            $this->setError(Text::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));

            return false;
        }

        // Set publish_up, publish_down to null if not set
        if (!$this->publish_up)
        {
            $this->publish_up = null;
        }

        if (!$this->publish_down)
        {
            $this->publish_down = null;
        }

        if (!$this->modified)
        {
            $this->modified = $this->created;
        }

        if (empty($this->modified_by))
        {
            $this->modified_by = $this->created_by;
        }

        return true;
    }
}