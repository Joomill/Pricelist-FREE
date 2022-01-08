<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;

/**
 * Script file of Pricelist Component
 *
 * @since  4.0.0
 */
class Com_PricelistInstallerScript
{
    /**
     * Minimum Joomla version to check
     *
     * @var    string
     * @since  4.0.0
     */
    private $minimumJoomlaVersion = '4.0';

    /**
     * Minimum PHP version to check
     *
     * @var    string
     * @since  4.0.0
     */
    private $minimumPHPVersion = JOOMLA_MINIMUM_PHP;

    /**
     * Method to install the extension
     *
     * @param InstallerAdapter $parent The class calling this method
     * @return  boolean  True on success
     * @since  4.0.0
     */
    public function install($parent): bool
    {
        //echo Text::_('COM_PRICELIST_INSTALLERSCRIPT_INSTALL');

        $db = Factory::getDbo();
        $alias = ApplicationHelper::stringURLSafe('Uncategorised');

        // Initialize a new category.
        $category = Table::getInstance('Category');

        $data = array(
            'extension' => 'com_pricelist',
            'title' => 'Uncategorised',
            'alias' => $alias,
            'description' => '',
            'published' => 1,
            'access' => 1,
            'params' => '{"target":"","image":""}',
            'metadesc' => '',
            'metakey' => '',
            'metadata' => '{"page_title":"","author":"","robots":""}',
            'created_time' => Factory::getDate()->toSql(),
            'created_user_id' => (int)$this->getAdminId(),
            'language' => '*',
            'rules' => array(),
            'parent_id' => 1,
        );

        $category->setLocation(1, 'last-child');

        // Bind the data to the table
        if (!$category->bind($data)) {
            return false;
        }

        // Check to make sure our data is valid.
        if (!$category->check()) {
            return false;
        }

        // Store the category.
        if (!$category->store(true)) {
            return false;
        }

        return true;
    }

    /**
     * Method to uninstall the extension
     *
     * @param InstallerAdapter $parent The class calling this method
     * @return  boolean  True on success
     * @since  4.0.0
     */
    public function uninstall($parent): bool
    {
        //echo Text::_('COM_PRICELIST_INSTALLERSCRIPT_UNINSTALL');
        return true;
    }

    /**
     * Method to update the extension
     *
     * @param InstallerAdapter $parent The class calling this method
     * @return  boolean  True on success
     * @since  4.0.0
     *
     */
    public function update($parent): bool
    {
        //echo Text::_('COM_PRICELIST_INSTALLERSCRIPT_UPDATE');
        return true;
    }

    /**
     * Function called before extension installation/update/removal procedure commences
     *
     * @param string $type The type of change (install, update or discover_install, not uninstall)
     * @param InstallerAdapter $parent The class calling this method
     * @return  boolean  True on success
     * @throws Exception
     * @since  4.0.0
     */
    public function preflight($type, $parent): bool
    {
        if ($type !== 'uninstall') {
            // Check for the minimum PHP version before continuing
            if (!empty($this->minimumPHPVersion) && version_compare(PHP_VERSION, $this->minimumPHPVersion, '<')) {
                Log::add(
                    Text::sprintf('JLIB_INSTALLER_MINIMUM_PHP', $this->minimumPHPVersion),
                    Log::WARNING,
                    'jerror'
                );
                return false;
            }
            // Check for the minimum Joomla version before continuing
            if (!empty($this->minimumJoomlaVersion) && version_compare(JVERSION, $this->minimumJoomlaVersion, '<')) {
                Log::add(
                    Text::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', $this->minimumJoomlaVersion),
                    Log::WARNING,
                    'jerror'
                );
                return false;
            }
        }
        //echo Text::_('COM_PRICELIST_INSTALLERSCRIPT_PREFLIGHT');
        return true;
    }

    /**
     * Function called after extension installation/update/removal procedure commences
     *
     * @param string $type The type of change (install, update or discover_install, not uninstall)
     * @param InstallerAdapter $parent The class calling this method
     * @return  boolean  True on success
     * @since  4.0.0
     */
    public function postflight($type, $parent)
    {
        //echo Text::_('COM_PRICELIST_INSTALLERSCRIPT_POSTFLIGHT');
        $this->saveContentTypes();
        return true;
    }

    /**
     * Retrieve the admin user id.
     *
     * @return  integer|boolean  One Administrator ID.
     * @since   4.0.0
     */
    private function getAdminId()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select the admin user ID
        $query
            ->clear()
            ->select($db->quoteName('u') . '.' . $db->quoteName('id'))
            ->from($db->quoteName('#__users', 'u'))
            ->join(
                'LEFT',
                $db->quoteName('#__user_usergroup_map', 'map')
                . ' ON ' . $db->quoteName('map') . '.' . $db->quoteName('user_id')
                . ' = ' . $db->quoteName('u') . '.' . $db->quoteName('id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__usergroups', 'g')
                . ' ON ' . $db->quoteName('map') . '.' . $db->quoteName('group_id')
                . ' = ' . $db->quoteName('g') . '.' . $db->quoteName('id')
            )
            ->where(
                $db->quoteName('g') . '.' . $db->quoteName('title')
                . ' = ' . $db->quote('Super Users')
            );

        $db->setQuery($query);
        $id = $db->loadResult();

        if (!$id || $id instanceof \Exception) {
            return false;
        }

        return $id;
    }

    /**
     * Adding content_type for tags.
     *
     * @return  integer|boolean  One Administrator ID.
     *
     * @since   4.0.0
     */
    private function saveContentTypes()
    {
        $table = Table::getInstance('Contenttype', 'JTable');

        $table->load(array('type_alias' => 'com_pricelist.product'));

        $tablestring = '{
			"special": {
			  "dbtable": "#__pricelist_products",
			  "key": "id",
			  "type": "ProductTable",
			  "prefix": "Joomill\\\\Component\\\\Pricelist\\\\Administrator\\\\Table\\\\",
			  "config": "array()"
			},
			"common": {
			  "dbtable": "#__ucm_content",
			  "key": "ucm_id",
			  "type": "Corecontent",
			  "prefix": "JTable",
			  "config": "array()"
			}
		  }';

        $fieldmapping = '{
			"common":{
                "core_content_item_id":"id",
                "core_title":"name",
                "core_state":"published",
                "core_alias":"alias",
                "core_created_time":"null",
                "core_modified_time":"null",
                "core_body":"description",
                "core_hits":"null",
                "core_publish_up":"publish_up",
                "core_publish_down":"publish_down",
                "core_access":"access",
                "core_params":"params",
                "core_featured":"featured",
                "core_metadata":"null",
                "core_language":"language",
                "core_images":"null",
                "core_urls":"null",
                "core_version":"null",
                "core_ordering":"ordering",
                "core_metakey":"null",
                "core_metadesc":"null",
                "core_catid":"catid",
                "core_xreference":"null",
                "asset_id":"null"
            },
            "special":{"price":"price"}
            }';
        {

            $contenttype = array();
            $contenttype['type_id'] = ($table->type_id) ? $table->type_id : 0;
            $contenttype['type_title'] = 'Items';
            $contenttype['type_alias'] = 'com_pricelist.product';
            $contenttype['table'] = $tablestring;
            $contenttype['rules'] = '';
            $contenttype['router'] = 'RouteHelper::getProductRoute';
            $contenttype['field_mappings'] = $fieldmapping;
            $contenttype['content_history_options'] = '{"formFile":"administrator\/components\/com_pricelist\/forms\/product.xml", "hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits", "ordering"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[
{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}';

            $table->save($contenttype);

            return;
        }
    }
}