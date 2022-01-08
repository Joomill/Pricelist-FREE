<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Administrator\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Item Model for a Pricelist.
 *
 * @since  4.0.0
 */
class ProductModel extends AdminModel
{
    /**
     * The type alias for this content type.
     *
     * @var    string
     * @since  4.0.0
     */
    public $typeAlias = 'com_pricelist.product';

    /**
     * The context used for the associations table
     *
     * @var    string
     * @since  4.0.0
     */
    protected $associationsContext = 'com_pricelist.item';

    /**
     * Batch copy/move command. If set to false, the batch copy/move command is not supported
     *
     * @var  string
     */
    protected $batch_copymove = 'category_id';

    /**
     * Allowed batch commands
     *
     * @var array
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'language_id' => 'batchLanguage',
        'tag' => 'batchTag',
        'user_id' => 'batchUser',
    ];

    /**
     * Method to get the row form.
     *
     * @param array $data Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  \JForm|boolean  A \JForm object on success, false on failure
     *
     * @since   4.0.0
     */

    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->typeAlias, 'product', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form))
        {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   4.0.0
     */
    protected function loadFormData()
    {


        $app = Factory::getApplication();

        // Check the session for previously entered form data.
        $data = $app->getUserState('com_pricelist.edit.product.data', array());

        if (empty($data))
        {
            $data = $this->getItem();

            // Prime some default values.
            if ($this->getState('product.id') == 0)
            {
                $data->set('catid', $app->input->get('catid', $app->getUserState('com_pricelist.products.filter.category_id'), 'int'));
            }
        }

        $this->preprocessData('com_pricelist.product', $data);

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param integer $pk The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     *
     * @since   1.6
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        // Load associated items
        $assoc = Associations::isEnabled();

        if ($assoc)
        {
            $item->associations = array();

            if ($item->id != null)
            {
                $associations = Associations::getAssociations('com_pricelist', '#__pricelist_products', 'com_pricelist.item', $item->id);

                foreach ($associations as $tag => $association)
                {
                    $item->associations[$tag] = $association->id;
                }
            }
        }

        // Load item tags
        if (!empty($item->id))
        {
            $item->tags = new TagsHelper;
            $item->tags->getTagIds($item->id, 'com_pricelist.product');
        }

        return $item;
    }


    /**
     * Method to toggle the featured setting of items.
     *
     * @param array $pks The ids of the items to toggle.
     * @param integer $value The value to toggle to.
     *
     * @return  boolean  True on success.
     *
     * @since   4.0.0
     */
    public function featured($pks, $value = 0)
    {
        // Sanitize the ids.
        $pks = ArrayHelper::toInteger((array)$pks);

        if (empty($pks))
        {
            $this->setError(Text::_('COM_PRICELIST_NO_ITEM_SELECTED'));

            return false;
        }

        $table = $this->getTable();

        try
        {
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__pricelist_products'));
            $query->set($db->quoteName('featured') . ' = :featured');
            $query->whereIn($db->quoteName('id'), $pks);
            $query->bind(':featured', $value, ParameterType::INTEGER);

            $db->setQuery($query);

            $db->execute();
        }
        catch (\Exception $e)
        {
            $this->setError($e->getMessage());

            return false;
        }

        $table->reorder();

        // Clean component's cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to save the form data.
     *
     * @param array $data The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   3.0
     */
    public function save($data)
    {
        $input = Factory::getApplication()->input;

        // Create new category, if needed.
        $createCategory = true;

        // If category ID is provided, check if it's valid.
        if (is_numeric($data['catid']) && $data['catid'])
        {
            $createCategory = !CategoriesHelper::validateCategoryId($data['catid'], 'com_pricelist');
        }

        // Save New Category
        if ($createCategory && $this->canCreateCategory())
        {
            $category = [
                // Remove #new# prefix, if exists.
                'title' => strpos($data['catid'], '#new#') === 0 ? substr($data['catid'], 5) : $data['catid'],
                'parent_id' => 1,
                'extension' => 'com_pricelist',
                'language' => $data['language'],
                'published' => 1,
            ];

            /** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $categoryModel */
            $categoryModel = Factory::getApplication()->bootComponent('com_categories')
                ->getMVCFactory()->createModel('Category', 'Administrator', ['ignore_request' => true]);

            // Create new category.
            if (!$categoryModel->save($category))
            {
                $this->setError($categoryModel->getError());

                return false;
            }

            // Get the Category ID.
            $data['catid'] = $categoryModel->getState('category.id');
        }

        // Alter the name for save as copy
        if ($input->get('task') == 'save2copy')
        {
            $origTable = clone $this->getTable();
            $origTable->load($input->getInt('id'));

            if ($data['name'] == $origTable->name)
            {
                list($name, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['name']);
                $data['name'] = $name;
                $data['alias'] = $alias;
            }
            else
            {
                if ($data['alias'] == $origTable->alias)
                {
                    $data['alias'] = '';
                }
            }

            $data['published'] = 0;
        }
        return parent::save($data);
    }

    /**
     * Preprocess the form.
     *
     * @param \JForm $form Form object.
     * @param object $data Data object.
     * @param string $group Group name.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function preprocessForm(\JForm $form, $data, $group = 'product')
    {
        if (Associations::isEnabled())
        {
            $languages = LanguageHelper::getContentLanguages(false, true, null, 'ordering', 'asc');

            if (count($languages) > 1)
            {
                $addform = new \SimpleXMLElement('<form />');
                $fields = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language)
                {
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'modal_product');
                    $field->addAttribute('language', $language->lang_code);
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('translate_label', 'false');
                    $field->addAttribute('select', 'true');
                    $field->addAttribute('new', 'true');
                    $field->addAttribute('edit', 'true');
                    $field->addAttribute('clear', 'true');
                }

                $form->load($addform, false);
            }
        }

        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param \Joomla\CMS\Table\Table $table The Table object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function prepareTable($table)
    {
        $table->generateAlias();
    }
}