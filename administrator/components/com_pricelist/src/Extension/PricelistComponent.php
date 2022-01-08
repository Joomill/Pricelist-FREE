<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Component\Pricelist\Administrator\Extension;

// No direct access.
defined('_JEXEC') or die;

use Joomill\Component\Pricelist\Administrator\Service\HTML\AdministratorService;
use Joomill\Component\Pricelist\Administrator\Service\HTML\Icon;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Association\AssociationServiceTrait;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_pricelist
 *
 * @since  4.0.0
 */
class PricelistComponent extends MVCComponent implements BootableExtensionInterface, CategoryServiceInterface, AssociationServiceInterface, RouterServiceInterface
{
    use CategoryServiceTrait;
    use AssociationServiceTrait;
    use HTMLRegistryAwareTrait;
    use RouterServiceTrait;

    /**
     * Booting the extension. This is the function to set up the environment of the extension like
     * registering new class loaders, etc.
     *
     * If required, some initial set up can be done from services of the container, eg.
     * registering HTML services.
     *
     * @param   ContainerInterface $container The container
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function boot(ContainerInterface $container)
    {
        $this->getRegistry()->register('pricelistadministrator', new AdministratorService);
        $this->getRegistry()->register('pricelisticon', new Icon($container->get(SiteApplication::class)));
    }

    /**
     * Adds Count Items for Category Manager.
     *
     * @param \stdClass[] $items The category objects
     * @param string $section The section
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function countItems(array $items, string $section)
    {
        try
        {
            $config = (object)[
                'related_tbl' => $this->getTableNameForSection($section),
                'state_col' => 'published',
                'group_col' => 'catid',
                'relation_type' => 'category_or_group',
            ];

            ContentHelper::countRelations($items, $config);
        }
        catch (\Exception $e)
        {
            // Ignore it
        }
    }

    /**
     * Returns the table for the count items functions for the given section.
     *
     * @param string $section The section
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    protected function getTableNameForSection(string $section = null)
    {
        return ($section === 'category' ? 'categories' : 'pricelist_products');
    }

    /**
     * Returns the state column for the count items functions for the given section.
     *
     * @param string $section The section
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    protected function getStateColumnForSection(string $section = null)
    {
        return 'published';
    }
}