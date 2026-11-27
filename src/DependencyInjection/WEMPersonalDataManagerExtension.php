<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class WEMPersonalDataManagerExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Legacy encryption key, stored in the localconfig by previous versions.
     */
    public const string LEGACY_ENCRYPTION_KEY = 'wem_pdm_encryption_key';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }

    /**
     * Personal data already stored have been encrypted with the key defined in the
     * localconfig: forward it to the encryption utility unless a key has been
     * explicitly configured for it.
     */
    public function prepend(ContainerBuilder $container): void
    {
        foreach ($container->getExtensionConfig('wem_utils') as $config) {
            if (! empty($config['encryption_key'])) {
                return;
            }
        }

        $encryptionKey = $this->getLegacyEncryptionKey((string) $container->getParameter('kernel.project_dir'));

        if ($encryptionKey !== null) {
            $container->prependExtensionConfig('wem_utils', [
                'encryption_key' => $encryptionKey,
            ]);
        }
    }

    private function getLegacyEncryptionKey(string $projectDir): string|null
    {
        $localConfig = $projectDir . '/system/config/localconfig.php';

        if (! file_exists($localConfig)) {
            return null;
        }

        $backup = $GLOBALS['TL_CONFIG'] ?? null;

        try {
            include $localConfig;
            $encryptionKey = $GLOBALS['TL_CONFIG'][self::LEGACY_ENCRYPTION_KEY] ?? null;
        } finally {
            if ($backup === null) {
                unset($GLOBALS['TL_CONFIG']);
            } else {
                $GLOBALS['TL_CONFIG'] = $backup;
            }
        }

        return \is_string($encryptionKey) && $encryptionKey !== '' ? $encryptionKey : null;
    }
}
