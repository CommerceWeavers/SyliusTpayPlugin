<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $container): void {
    $container->extension('liip_imagine', [
        'filter_sets' => [
            'cw_sylius_tpay_admin_payment_method_image' => [
                'filters' => [
                    'thumbnail' => ['size' => [150, 100], 'mode' => 'outbound']
                ],
            ],
            'cw_sylius_tpay_shop_payment_method_image' => [
                'filters' => [
                    'thumbnail' => ['size' => [32, 32], 'mode' => 'outbound']
                ],
            ],
        ],
    ]);
};
