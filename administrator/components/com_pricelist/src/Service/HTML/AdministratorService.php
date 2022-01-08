<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Administrator\Service\HTML;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;


/**
 * Pricelist HTML class.
 *
 * @since  4.0.0
 */
class AdministratorService
{

    /**
     * Get the associated language flags
     *
     * @param   integer $productid The item id to search associations
     *
     * @return  string  The language HTML
     *
     * @throws  Exception
     *
     * @since   4.0.0
     */
    public function association($productid)
    {
        // Defaults
        $html = '';

        // Get the associations
        if ($associations = Associations::getAssociations('com_pricelist', '#__pricelist_products', 'com_pricelist.item', $productid))
        {
            foreach ($associations as $tag => $associated)
            {
                $associations[$tag] = (int)$associated->id;
            }

            // Get the associated items
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('c.id'),
                        $db->quoteName('c.name', 'title'),
                        $db->quoteName('l.sef', 'lang_sef'),
                        $db->quoteName('lang_code'),
                        $db->quoteName('cat.title', 'category_title'),
                        $db->quoteName('l.image'),
                        $db->quoteName('l.title', 'language_title'),
                    ]
                )
                ->from($db->quoteName('#__pricelist_products', 'c'))
                ->join('LEFT', $db->quoteName('#__categories', 'cat'), $db->quoteName('cat.id') . ' = ' . $db->quoteName('c.catid'))
                ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('c.language') . ' = ' . $db->quoteName('l.lang_code'))
                ->whereIn($db->quoteName('c.id'), array_values($associations))
                ->where($db->quoteName('c.id') . ' != :id')
                ->bind(':id', $productid, ParameterType::INTEGER);
            $db->setQuery($query);

            try
            {
                $items = $db->loadObjectList('id');
            }
            catch (\RuntimeException $e)
            {
                throw new \Exception($e->getMessage(), 500, $e);
            }

            if ($items)
            {
                $languages = LanguageHelper::getContentLanguages(array(0, 1));
                $content_languages = array_column($languages, 'lang_code');

                foreach ($items as &$item)
                {
                    if (in_array($item->lang_code, $content_languages))
                    {
                        $text = $item->lang_code;
                        $url = Route::_('index.php?option=com_pricelist&task=product.edit&id=' . (int)$item->id);
                        $tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
                            . htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title);
                        $classes = 'badge bg-secondary';

                        $item->link = '<a href="' . $url . '" class="' . $classes . '">' . $text . '</a>'
                            . '<div role="tooltip" id="tip-' . (int)$productid . '-' . (int)$item->id . '">' . $tooltip . '</div>';
                    }
                    else
                    {
                        // Display warning if Content Language is trashed or deleted
                        Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_ASSOCIATIONS_CONTENTLANGUAGE_WARNING', $item->lang_code), 'warning');
                    }
                }
            }

            $html = LayoutHelper::render('joomla.content.associations', $items);
        }

        return $html;
    }

    /**
     * Show the featured/not-featured icon.
     *
     * @param integer $value The featured value.
     * @param integer $i Id of the item.
     * @param boolean $canChange Whether the value can be changed or not.
     *
     * @return  string    The anchor tag to toggle featured/unfeatured items.
     *
     * @since   4.0.0
     */
    public function featured($value, $i, $canChange = true)
    {
        // Array of image, task, title, action
        $states = [
            0 => ['unfeatured', 'products.featured', 'COM_PRICELIST_UNFEATURED', 'JGLOBAL_ITEM_FEATURE'],
            1 => ['featured', 'products.unfeatured', 'JFEATURED', 'JGLOBAL_ITEM_UNFEATURE'],
        ];
        $state = ArrayHelper::getValue($states, (int)$value, $states[1]);
        $icon = $state[0] === 'featured' ? 'star featured' : 'star';

        if ($canChange)
        {
            $html = '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="tbody-icon'
                . ($value == 1 ? ' active' : '') . '" aria-labelledby="cb' . $i . '-desc">'
                . '<span class="fas fa-' . $icon . '" aria-hidden="true"></span></a>'
                . '<div role="tooltip" id="cb' . $i . '-desc">' . Text::_($state[3]);
        }
        else
        {
            $html = '<a class="tbody-icon disabled' . ($value == 1 ? ' active' : '')
                . '" title="' . Text::_($state[2]) . '"><span class="fas fa-' . $icon . '" aria-hidden="true"></span></a>';
        }

        return $html;
    }
}