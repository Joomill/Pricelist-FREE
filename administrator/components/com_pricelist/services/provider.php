<?php
/*
 *  package: Joomla Price List component
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

use Joomill\Component\Pricelist\Administrator\Extension\PricelistComponent;
use Joomill\Component\Pricelist\Administrator\Helper\AssociationsHelper;
use Joomla\CMS\Association\AssociationExtensionInterface;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The pricelist service provider.
 *
 * @since   4.0.0
 */
return new class implements ServiceProviderInterface
{

    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container $container The DI container.
     *
     * @return  void
     *
     * @since   4.0.0
     */
	public function register(Container $container)
    {
        $container->set(AssociationExtensionInterface::class, new AssociationsHelper);

        $container->registerServiceProvider(new CategoryFactory('\\Joomill\\Component\\Pricelist'));
        $container->registerServiceProvider(new MVCFactory('\\Joomill\\Component\\Pricelist'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomill\\Component\\Pricelist'));
        $container->registerServiceProvider(new RouterFactory('\\Joomill\\Component\\Pricelist'));

        $container->set(
            ComponentInterface::class,
            function (Container $container)
            {
                $component = new PricelistComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
                $component->setAssociationExtension($container->get(AssociationExtensionInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};