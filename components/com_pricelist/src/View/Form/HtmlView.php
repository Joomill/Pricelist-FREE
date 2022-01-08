<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Site\View\Form;

// No direct access.
defined('_JEXEC') or die;

use Joomill\Component\Pricelist\Administrator\Helper\ProductHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * HTML Product View class for the Pricelist component
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var    \Joomla\CMS\Form\Form
     * @since  4.0.0
     */
    protected $form;

    /**
     * @var    object
     * @since  4.0.0
     */
    protected $item;

    /**
     * @var    string
     * @since  4.0.0
     */
    protected $return_page;

    /**
     * @var    string
     * @since  4.0.0
     */
    protected $pageclass_sfx;

    /**
     * @var    \Joomla\Registry\Registry
     * @since  4.0.0
     */
    protected $state;

    /**
     * @var    \Joomla\Registry\Registry
     * @since  4.0.0
     */
    protected $params;

    /**
     * Execute and display a template script.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void|boolean
     *
     * @throws \Exception
     * @since  4.0.0
     */
    public function display($tpl = null)
    {
        $user = Factory::getUser();
        $app = Factory::getApplication();

        // Get model data.
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');
        $this->return_page = $this->get('ReturnPage');

        if (empty($this->item->id))
        {
            $authorised = $user->authorise('core.create', 'com_pricelist') || count($user->getAuthorisedCategories('com_pricelist', 'core.create'));
        }
        else
        {
            // Since we don't track these assets at the item level, use the category id.
            $canDo = ContentHelper::getActions('com_pricelist', 'category', $this->item->catid);
            $authorised = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by === $user->id);
        }

        if ($authorised !== true)
        {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $app->setHeader('status', 403, true);

            return false;
        }

        $this->item->tags = new TagsHelper;

        if (!empty($this->item->id))
        {
            $this->item->tags->getItemTags('com_pricelist.product', $this->item->id);
        }

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            $app->enqueueMessage(implode("\n", $errors), 'error');

            return false;
        }

        // Create a shortcut to the parameters.
        $this->params = $this->state->params;

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Override global params with pricelist specific params
        $this->params->merge($this->item->params);

        // Propose current language as default when creating new pricelist
        if (empty($this->item->id) && Multilanguage::isEnabled())
        {
            $lang = Factory::getLanguage()->getTag();
            $this->form->setFieldAttribute('language', 'default', $lang);
        }

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     *
     * @throws \Exception
     *
     * @since  4.0.0
     */
    protected function _prepareDocument()
    {
        $app = Factory::getApplication();

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $app->getMenu()->getActive();

        if ($menu)
        {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else
        {
            $this->params->def('page_heading', Text::_('COM_PRICELIST_FORM_EDIT_PRODUCT'));
        }

        $title = $this->params->def('page_title', Text::_('COM_PRICELIST_FORM_EDIT_PRODUCT'));

        $this->setDocumentTitle($title);

        $pathway = $app->getPathWay();
        $pathway->addItem($title, '');

    }
}
