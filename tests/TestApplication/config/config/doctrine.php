<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container): void {
    $container->extension('doctrine', [
        'orm' => [
            'entity_managers' => [
                'default' => [
                    'mappings' => [
                        'TestApp' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => realpath(__DIR__ . '/../../src/Entity'),
                            'prefix' => 'TestApp',
                        ],
                    ],
                ],
            ],
        ],
    ]);
};
