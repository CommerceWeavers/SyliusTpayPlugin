<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class CommerceWeaversSyliusTpayExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    private const ALIAS = 'commerce_weavers_sylius_tpay';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $config = $this->getCurrentConfiguration($container);

        $this->registerResources(
            self::ALIAS,
            'doctrine/orm',
            $config['resources'],
            $container,
        );

        $this->prependDoctrineMigrations($container);
        $this->prependDoctrineMapping($container);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'CommerceWeaversSyliusTpayMigrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@CommerceWeaversSyliusTpayPlugin/migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [
            'Sylius\Bundle\CoreBundle\Migrations',
        ];
    }

    private function prependDoctrineMapping(ContainerBuilder $container): void
    {
        $config = array_merge(...$container->getExtensionConfig('doctrine'));

        // Do not register mappings if dbal not configured.
        if (!isset($config['dbal']) || !isset($config['orm'])) {
            return;
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'CommerceWeaversSyliusTpayPluginBlikPayment' => [
                        'type' => 'xml',
                        'dir' => $this->getPath($container, '/config/doctrine/blik_payment/'),
                        'is_bundle' => false,
                        'prefix' => 'CommerceWeavers\SyliusTpayPlugin\BlikPayment\Entity',
                        'alias' => 'CommerceWeaversSyliusTpayPluginBlikPayment',
                    ],
                    'CommerceWeaversSyliusTpayPluginCardPayment' => [
                        'type' => 'xml',
                        'dir' => $this->getPath($container, '/config/doctrine/card_payment/'),
                        'is_bundle' => false,
                        'prefix' => 'CommerceWeavers\SyliusTpayPlugin\CardPayment\Entity',
                        'alias' => 'CommerceWeaversSyliusTpayPluginCardPayment',
                    ],
                    'CommerceWeaversSyliusTpayPlugin' => [
                        'type' => 'xml',
                        'dir' => $this->getPath($container, '/config/doctrine/shared/'),
                        'is_bundle' => false,
                        'prefix' => 'CommerceWeavers\SyliusTpayPlugin\Entity',
                        'alias' => 'CommerceWeaversSyliusTpayPlugin',
                    ],
                ],
            ],
        ]);
    }

    private function getPath(ContainerBuilder $container, string $path): string
    {
        /** @var array<string, array<string, string>> $metadata */
        $metadata = $container->getParameter('kernel.bundles_metadata');

        return $metadata['CommerceWeaversSyliusTpayPlugin']['path'] . $path;
    }

    private function getCurrentConfiguration(ContainerBuilder $container): array
    {
        $configuration = $this->getConfiguration([], $container);
        assert($configuration !== null);

        $configs = $container->getExtensionConfig($this->getAlias());

        return $this->processConfiguration($configuration, $configs);
    }
}
