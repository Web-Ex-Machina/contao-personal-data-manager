<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * Copyright (c) 2015-2024 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-smartgear
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/personal-data-manager/
 */

namespace WEM\PersonalDataManagerBundle\DependencyInjection;

use Contao\Config;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Adds the bundle services to the container.
 *
 * @author Web ex Machina <https://www.webexmachina.fr>
 */
class WEMPersonalDataManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');
        $loader->load('controllers.yml');
        $loader->load('routing.yml');

        if (!$container->hasParameter('wem_contao_encryption.encryption_key') || null === $container->getParameter('wem_contao_encryption.encryption_key')) {
            $projectDir = $container->getParameter('kernel.project_dir');
            if (file_exists($projectDir.'/system/config/localconfig.php')) {
                include $projectDir.'/system/config/localconfig.php';
            }

            if (null !== Config::get('wem_pdm_encryption_key')) {
                $container->setParameter('wem_contao_encryption.encryption_key', Config::get('wem_pdm_encryption_key'));
            }
        }
    }
}
