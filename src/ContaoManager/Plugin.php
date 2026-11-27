<?php

declare(strict_types=1);

namespace WEM\PersonalDataManagerBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;
use WEM\PersonalDataManagerBundle\WEMPersonalDataManagerBundle;
use WEM\UtilsBundle\UtilsBundle;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(WEMPersonalDataManagerBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, UtilsBundle::class])
                ->setReplace(['wem-personal-data-manager']),
        ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): RouteCollection|null
    {
        return $resolver
            ->resolve(__DIR__ . '/../../config/routes.yaml')
            ->load(__DIR__ . '/../../config/routes.yaml')
        ;
    }
}
