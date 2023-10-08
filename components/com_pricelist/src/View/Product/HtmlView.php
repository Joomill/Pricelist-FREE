<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2023. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

namespace Joomill\Component\Pricelist\Site\View\Product;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;


/**
 * HTML Product View class for the Pricelist component
 *
 * @since 4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The page parameters
     *
     * @var    \Joomla\Registry\Registry|null
     * @since 4.0.0
     */
    protected $params = null;

    /**
     * The item model state
     *
     * @var    \Joomla\Registry\Registry
     * @since 4.0.0
     */
    protected $state;

    /**
     * The item object details
     *
     * @var    \JObject
     * @since 4.0.0
     */
    protected $item;

    /**
     * Execute and display a template script.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        $item = $this->item = $this->get('Item');

        $state = $this->State = $this->get('State');
        $params = $this->Params = $state->get('params');
        $itemparams = new Registry(json_decode($item->params));

        $temp = clone $params;

        // Create a shortcut for $item.
        $item = $this->item;
        $item->tagLayout = new FileLayout('joomla.content.tags');

        /**
         * $item->params are the foo params, $temp are the menu item params
         * Merge so that the menu item params take priority
         *
         * $itemparams->merge($temp);
         */

        // Merge so that foo params take priority
        $temp->merge($itemparams);
        $item->params = $temp;

        Factory::getApplication()->triggerEvent('onContentPrepare', array('com_pricelist.product', &$item));

        // Store the events for later
        $item->event = new \stdClass;
        $results = Factory::getApplication()->triggerEvent('onContentAfterTitle', array('com_pricelist.product', &$item, &$item->params));
        $item->event->afterDisplayTitle = trim(implode("\n", $results));

        $results = Factory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_pricelist.product', &$item, &$item->params));
        $item->event->beforeDisplayContent = trim(implode("\n", $results));

        $results = Factory::getApplication()->triggerEvent('onContentAfterDisplay', array('com_pricelist.product', &$item, &$item->params));
        $item->event->afterDisplayContent = trim(implode("\n", $results));

        return parent::display($tpl);
    }
}