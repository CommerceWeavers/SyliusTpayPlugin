<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Fixture\Factory\PaymentMethodExampleFactory;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.fixture.factory.payment_method_example', PaymentMethodExampleFactory::class)
        ->decorate('sylius.fixture.example_factory.payment_method')
        ->args([
            service('.inner'),
            service('payum.dynamic_gateways.cypher'),
        ])
    ;
};
