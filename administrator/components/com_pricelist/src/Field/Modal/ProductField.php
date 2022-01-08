<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Administrator\Field\Modal;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * Supports a modal product picker.
 *
 * @since  4.0.0
 */
class ProductField extends FormField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   4.0.0
     */
    protected $type = 'Modal_Product';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   4.0.0
     */
    protected function getInput()
    {
        $allowClear = ((string)$this->element['clear'] != 'false');
        $allowSelect = ((string)$this->element['select'] != 'false');

        // The active product id field.
        $value = (int)$this->value > 0 ? (int)$this->value : '';

        // Create the modal id.
        $modalId = 'Product_' . $this->id;

        // Add the modal field script to the document head.
        HTMLHelper::_(
            'script',
            'system/fields/modal-fields.min.js',
            ['version' => 'auto', 'relative' => true]
        );

        // Script to proxy the select modal function to the modal-fields.js file.
        if ($allowSelect)
        {
            static $scriptSelect = null;

            if (is_null($scriptSelect))
            {
                $scriptSelect = [];
            }

            if (!isset($scriptSelect[$this->id]))
            {
                Factory::getDocument()->addScriptDeclaration("
				function jSelectProduct_"
                    . $this->id
                    . "(id, title, object) { window.processModalSelect('Product', '"
                    . $this->id . "', id, title, '', object);}");

                $scriptSelect[$this->id] = true;
            }
        }

        // Setup variables for display.
        $linkProducts = 'index.php?option=com_pricelist&amp;view=products&amp;layout=modal&amp;tmpl=component&amp;'
            . Session::getFormToken() . '=1';
        $modalTitle = Text::_('COM_PRICELIST_SELECT_A_PRODUCT');

        if (isset($this->element['language']))
        {
            $linkProducts .= '&amp;forcedLanguage=' . $this->element['language'];
            $modalTitle .= ' &#8212; ' . $this->element['label'];
        }

        $urlSelect = $linkProducts . '&amp;function=jSelectProduct_' . $this->id;

        if ($value)
        {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('name'))
                ->from($db->quoteName('#__pricelist_products'))
                ->where($db->quoteName('id') . ' = ' . (int)$value);
            $db->setQuery($query);

            try
            {
                $title = $db->loadResult();
            }
            catch (\RuntimeException $e)
            {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $title = empty($title) ? Text::_('COM_PRICELIST_SELECT_A_PRODUCT') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        // The current product display field.
        $html = '';

        if ($allowSelect || $allowNew || $allowEdit || $allowClear)
        {
            $html .= '<span class="input-group">';
        }

        $html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $title . '" readonly size="35">';

        // Select product button
        if ($allowSelect)
        {
            $html .= '<button'
                . ' class="btn btn-primary hasTooltip' . ($value ? ' hidden' : '') . '"'
                . ' id="' . $this->id . '_select"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalSelect' . $modalId . '"'
                . ' title="' . HTMLHelper::tooltipText('COM_PRICELIST_SELECT_A_PRODUCT') . '">'
                . '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
                . '</button>';
        }

        // Clear product button
        if ($allowClear)
        {
            $html .= '<button'
                . ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_clear"'
                . ' type="button"'
                . ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
                . '<span class="icon-remove" aria-hidden="true"></span>' . Text::_('JCLEAR')
                . '</button>';
        }

        if ($allowSelect || $allowNew || $allowEdit || $allowClear)
        {
            $html .= '</span>';
        }

        // Select product modal
        if ($allowSelect)
        {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalSelect' . $modalId,
                [
                    'title' => $modalTitle,
                    'url' => $urlSelect,
                    'height' => '400px',
                    'width' => '800px',
                    'bodyHeight' => 70,
                    'modalWidth' => 80,
                    'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
                ]
            );
        }

        // Note: class='required' for client side validation.
        $class = $this->required ? ' class="required modal-value"' : '';

        $html .= '<input type="hidden" id="'
            . $this->id . '_id"'
            . $class . ' data-required="' . (int)$this->required
            . '" name="' . $this->name
            . '" data-text="'
            . htmlspecialchars(Text::_('COM_PRICELIST_SELECT_A_PRODUCT', true), ENT_COMPAT, 'UTF-8')
            . '" value="' . $value . '">';

        return $html;
    }

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   4.0.0
     */
    protected function getLabel()
    {
        return str_replace($this->id, $this->id . '_name', parent::getLabel());
    }
}
