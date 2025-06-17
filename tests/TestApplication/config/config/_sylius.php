<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use TestApp\Entity\Order;
use TestApp\Entity\PaymentMethod;
use TestApp\Repository\PaymentMethodRepository;

return static function(ContainerConfigurator $container): void {
    $container->extension('sylius_order', [
        'resources' => [
            'order' => [
                'classes' => [
                    'model' => Order::class,
                ],
            ],
        ],
    ]);
    $container->extension('sylius_payment', [
        'resources' => [
            'payment_method' => [
                'classes' => [
                    'model' => PaymentMethod::class,
                    'repository' => PaymentMethodRepository::class,
                ],
            ],
        ],
    ]);
};
