<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Fixture\Factory\PaymentMethodExampleFactory;
use CommerceWeavers\SyliusTpayPlugin\Fixture\PaymentMethodFixture;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.fixture.factory.payment_method_example', PaymentMethodExampleFactory::class)
        ->decorate('sylius.fixture.example_factory.payment_method')
        ->args([
            service('payum.dynamic_gateways.cypher')->nullOnInvalid(),
            service('sylius.factory.payment_method'),
            service('sylius.repository.locale'),
            service('sylius.repository.channel'),
        ])
    ;

    $services->set('commerce_weavers_sylius_tpay.fixture.payment_method', PaymentMethodFixture::class)
        ->decorate('sylius.fixture.payment_method')
        ->args([
            service('sylius.manager.payment_method'),
            service('sylius.fixture.example_factory.payment_method'),
        ])
    ;
};
