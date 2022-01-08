<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Administrator\View\Product;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit a product.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
    * The \JForm object
    *
    * @var  \JForm
    */
    protected $form;

    /**
     * The active item
     *
     * @var  object
     */
    protected $item;

    /**
     * The model state
     *
     * @var  \JObject
     */
    protected $state;

    /**
     * Display the view.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // If we are forcing a language in modal (used for associations).
        if ($this->getLayout() === 'modal' && $forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'cmd'))
        {
            // Set the language field to the forcedLanguage and disable changing it.
            $this->form->setValue('language', null, $forcedLanguage);
            $this->form->setFieldAttribute('language', 'readonly', 'true');

            // Only allow to select categories with All language or with the forced language.
            $this->form->setFieldAttribute('catid', 'language', '*,' . $forcedLanguage);

            // Only allow to select tags with All language or with the forced language.
            $this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
        }

        $this->addToolbar();

        return parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function addToolbar()
    {
        $app = Factory::getApplication();
        $app->input->set('hidemainmenu', true);

        $user = $app->getIdentity();
        $userId = $user->id;

        $isNew = ($this->item->id == 0);

        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(
            Text::_('COM_PRICELIST_' . ($isNew ? 'ADD_PRODUCT' : 'EDIT_PRODUCT')),
            'pencil-alt article-add'
        );

        $toolbar->apply('product.apply');
        $saveGroup = $toolbar->dropdownButton('save-group');
        $saveGroup->configure(
            function (Toolbar $childBar) use ($user)
            {
                $childBar->save('product.save');
                $childBar->save2new('product.save2new');

                // If checked out, we can still save
                if ($this->item->id != 0)
                {
                    $childBar->save2copy('product.save2copy');
                }
            }
        );

        $toolbar->cancel('product.cancel', 'JTOOLBAR_CLOSE');

        $toolbar->help('', false, 'https://www.joomill-extensions.com/extensions/price-list-component/documentation');

    }
}