<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerBuilder;

return function(ContainerConfigurator $container, ContainerBuilder $containerBuilder): void {
    $kernelBundles = $containerBuilder->getParameter('kernel.bundles');

    if (!isset($kernelBundles['winzouStateMachineBundle'])) {
        return;
    }

    $container->extension('winzou_state_machine', [
        'sylius_payment' => [
            'callbacks' => [
                'before' => [
                    'tpay_refund_payment' => [
                        'on' => ['refund'],
                        'do' => ['@commerce_weavers_sylius_tpay.refunding.dispatcher.refund', 'dispatch'],
                        'args' => ['object'],
                    ],
                ],
            ],
        ],
    ]);

    if (isset($kernelBundles['SyliusRefundPlugin'])) {
        $container->extension('winzou_state_machine', [
            'sylius_refund_refund_payment' => [
                'callbacks' => [
                    'before' => [
                        'tpay_refund_payment' => [
                            'on' => ['complete'],
                            'do' => ['@commerce_weavers_sylius_tpay.refunding.dispatcher.refund', 'dispatch'],
                            'args' => ['object'],
                        ],
                    ]
                ]
            ],
        ]);
    }
};
