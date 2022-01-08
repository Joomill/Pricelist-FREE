<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Pricelist Plugin
 *
 * @package Pricelist
 *
 * @since 4.0.0
 */
class plgSystemPricelist extends CMSPlugin
{

    /**
     * @var
     * @since 4.0.0
     */
    protected $app;

    /**
     * @var bool
     * @since 4.0.0
     */
    protected $autoloadLanguage = true;


    /**
     * @param $subject
     * @param $config
     *
     * @since 4.0.0
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
     * @param $form
     * @param $data
     *
     * @return bool
     *
     * @throws Exception
     * @since 4.0.0
     */
    public function onContentPrepareForm($form, $data)
    {
        $app = Factory::getApplication();
        $option = $app->input->get('option');
        $context = $app->input->get('context');
        $extension = $app->input->get('extension');
        if (!($form instanceof JForm))
        {
            $this->_subject->setError('JERROR_NOT_A_FORM');
            return false;
        }

        if (($option == "com_fields") && ($context == "com_pricelist.product"))
        {
            Form::addFormPath(__DIR__ . '/params');
            $form->loadFile('fields_pricelist', false);
            return true;
        }

        if (($option == "com_categories") && ($extension == "com_pricelist")) {
            $doc = Factory::getDocument();
            $script = "	function hideedit() {
            document.getElementById('toolbar-contract').style.display = 'none';
            document.getElementById('associations').style.display = 'none';
            
            var t = document.getElementById('jform_language');
			t.value = '';
			t.parentNode.parentNode.style.display = 'none';
			
			var t = document.getElementById('jform_access');
			t.value = '';
			t.parentNode.parentNode.style.display = 'none';
			}
		window.onload = hideedit;";
            $doc->addScriptDeclaration($script);
        }
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      An optional array of data for the form to interogate.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form  A Form object on success, false on failure
     *
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_fields.field',
            'field',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );


        if (empty($form))
        {
            return false;
        }

        return $form;
    }

}