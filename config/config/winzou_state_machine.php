<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $container): void {
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
};
