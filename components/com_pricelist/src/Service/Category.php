<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Site\Service;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;

/**
 * Foo Component Category Tree
 *
 * @since  4.0.0
 */
class Category extends Categories
{
    /**
     * Class constructor
     *
     * @param array $options Array of options
     *
     * @since  4.0.0
     */
    public function __construct($options = array())
    {
        $options['table'] = '#__pricelist_products';
        $options['extension'] = 'com_pricelist';
        $options['statefield'] = 'published';

        parent::__construct($options);
    }
}