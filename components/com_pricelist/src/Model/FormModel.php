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

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Pricelist Component Product Model
 *
 * @since  4.0.0
 */
class FormModel extends \Joomill\Component\Pricelist\Administrator\Model\ProductModel
{
    /**
     * Model typeAlias string. Used for version history.
     *
     * @var  string
     * @since  4.0.0
     */
    public $typeAlias = 'com_pricelist.product';

    /**
     * Name of the form
     *
     * @var string
     * @since  4.0.0
     */
    protected $formName = 'form';

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
        $form = parent::getForm($data, $loadData);

        // Prevent messing with article language and category when editing existing product with associations
        if ($id = $this->getState('product.id') && Associations::isEnabled())
        {
            $associations = Associations::getAssociations('com_pricelist', '#__pricelist_products', 'com_pricelist.item', $id);

            // Make fields read only
            if (!empty($associations))
            {
                $form->setFieldAttribute('language', 'readonly', 'true');
                $form->setFieldAttribute('language', 'filter', 'unset');
            }
        }

        return $form;
    }

    /**
     * Method to get product data.
     *
     * @param integer $itemId The id of the product.
     *
     * @return  mixed  Pricelist item data object on success, false on failure.
     *
     * @throws  Exception
     *
     * @since   4.0.0
     */
    public function getItem($itemId = null)
    {
        $itemId = (int)(!empty($itemId)) ? $itemId : $this->getState('product.id');

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        try
        {
            if (!$table->load($itemId))
            {
                return false;
            }
        }
        catch (Exception $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage());

            return false;
        }

        $properties = $table->getProperties();
        $value = ArrayHelper::toObject($properties, 'JObject');

        // Convert field to Registry.
        $value->params = new Registry($value->params);

        // Convert the metadata field to an array.
        //$registry        = new Registry($value->metadata);
        //$value->metadata = $registry->toArray();

        if ($itemId)
        {
            $value->tags = new TagsHelper;
            $value->tags->getTagIds($value->id, 'com_pricelist.product');
            $value->metadata['tags'] = $value->tags;
        }

        return $value;
    }

    /**
     * Get the return URL.
     *
     * @return  string  The return URL.
     *
     * @since   4.0.0
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }

    /**
     * Method to save the form data.
     *
     * @param array $data The form data.
     *
     * @return  boolean  True on success.
     *
     * @throws Exception
     * @since   4.0.0
     */
    public function save($data)
    {
        // Associations are not edited in frontend ATM so we have to inherit them
        if (Associations::isEnabled() && !empty($data['id'])
            && $associations = Associations::getAssociations('com_pricelist', '#__pricelist_products', 'com_pricelist.item', $data['id']))
        {
            foreach ($associations as $tag => $associated)
            {
                $associations[$tag] = (int)$associated->id;
            }

            $data['associations'] = $associations;
        }

        return parent::save($data);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since  4.0.0
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load state from the request.
        $pk = $app->input->getInt('id');
        $this->setState('product.id', $pk);

        $this->setState('product.catid', $app->input->getInt('catid'));

        $return = $app->input->get('return', null, 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', $app->input->getString('layout'));
    }

    /**
     * Allows preprocessing of the JForm object.
     *
     * @param Form $form The form object
     * @param array $data The data to be merged into the form object
     * @param string $group The plugin group to be executed
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function preprocessForm(Form $form, $data, $group = 'product')
    {
        if (!Multilanguage::isEnabled())
        {
            $form->setFieldAttribute('language', 'type', 'hidden');
            $form->setFieldAttribute('language', 'default', '*');
        }

        return parent::preprocessForm($form, $data, $group);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $name The table name. Optional.
     * @param string $prefix The class prefix. Optional.
     * @param array $options Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @throws  \Exception
     * @since  4.0.0
     */
    public function getTable($name = 'Product', $prefix = 'Administrator', $options = array())
    {
        return parent::getTable($name, $prefix, $options);
    }
}
