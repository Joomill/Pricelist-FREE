<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Site\View\Category;

// No direct access.
defined('_JEXEC') or die;

use Joomill\Component\Pricelist\Site\Helper\RouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\CategoryView;

/**
 * HTML View class for the Foos component
 *
 * @since  4.0.0
 */
class HtmlView extends CategoryView
{
    /**
     * @var    string  The name of the extension for the category
     * @since  4.0.0
     */
    protected $extension = 'com_pricelist';

    /**
     * @var    string  Default title to use for page title
     * @since  4.0.0
     */
    protected $defaultPageTitle = 'COM_FOO_DEFAULT_PAGE_TITLE';

    /**
     * @var    string  The name of the view to link individual items to
     * @since  4.0.0
     */
    protected $viewName = 'product';

    /**
     * Run the standard Joomla plugins
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $runPlugins = true;

    /**
     * Execute and display a template script.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        parent::commonCategoryDisplay();

        $app = Factory::getApplication();

        // Flag indicates to not add limitstart=0 to URL
        $this->pagination->hideEmptyLimitstart = true;

        // Prepare the data.
        // Compute the foo slug.
        foreach ($this->items as $item)
        {
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
            $temp = $item->params;
            $item->params = clone $this->params;
            $item->params->merge($temp);


            $app->triggerEvent('onContentPrepare', array('com_pricelist.product', &$item, &$item->params, 0));

            $item->event = new \stdClass;
            $results = $app->triggerEvent('onContentAfterTitle', array('com_pricelist.product', &$item, &$item->params, 0));
            $item->event->afterDisplayTitle = trim(implode("\n", $results));

            $results = $app->triggerEvent('onContentBeforeDisplay', array('com_pricelist.product', &$item, &$item->params, 0));
            $item->event->beforeDisplayContent = trim(implode("\n", $results));

            $results = $app->triggerEvent('onContentAfterDisplay', array('com_pricelist.product', &$item, &$item->params, 0));
            $item->event->afterDisplayContent = trim(implode("\n", $results));
        }

        return parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     */
    protected function prepareDocument()
    {
        parent::prepareDocument();

        $menu = $this->menu;
        $id = (int)@$menu->query['id'];

        if ($menu && (!isset($menu->query['option']) || $menu->query['option'] != $this->extension || $menu->query['view'] == $this->viewName
                || $id != $this->category->id))
        {
            $path = array(array('title' => $this->category->title, 'link' => ''));
            $category = $this->category->getParent();

            while ((!isset($menu->query['option']) || $menu->query['option'] !== 'com_pricelist' || $menu->query['view'] === 'product'
                    || $id != $category->id) && $category->id > 1)
            {
                $path[] = array('title' => $category->title, 'link' => RouteHelper::getCategoryRoute($category->id, $category->language));
                $category = $category->getParent();
            }

            $path = array_reverse($path);

            foreach ($path as $item)
            {
                $this->pathway->addItem($item['title'], $item['link']);
            }
        }

        parent::addFeed();
    }
}
