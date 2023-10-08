<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2023. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

class JFormFieldPRO extends Joomla\CMS\Form\Field\ListField
{
    protected $type = 'pro';

    protected function getInput()
    {
        $text = Text::_('COM_PRICELIST_PRO_ONLY');
        return
            '<code>' . $text . '</code>';
    }
}