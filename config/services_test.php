<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Test\Calendar\SimpleDateTimeProvider;
use CommerceWeavers\SyliusTpayPlugin\Test\Payum\Cypher\FakeCypher;
use CommerceWeavers\SyliusTpayPlugin\Test\Payum\SimpleGetStatusFactory;
use CommerceWeavers\SyliusTpayPlugin\Test\Workflow\SimpleStateMachineFactory;
use Sylius\Calendar\Provider\DateTimeProviderInterface;

return function(ContainerConfigurator $container): void {
    $services = $container->services();
    
    // Fix missing parameter - compatibility with newer Sylius versions
    $container->parameters()->set('sylius.security.new_api_shop_route', '%sylius.security.api_shop_route%');

    $services->set('payum.dynamic_gateways.cypher', FakeCypher::class)
        ->args([
            env('PAYUM_CYPHER_KEY'),
        ])
    ;

    // Stub for DateTimeProvider used in BLIK alias functionality
    $services->set(DateTimeProviderInterface::class, SimpleDateTimeProvider::class);

    // Stub for state machine factory
    $services->set('sm.factory', SimpleStateMachineFactory::class);

    // Stub for Payum GetStatus factory
    $services->set('sylius.factory.payum_get_status_action', SimpleGetStatusFactory::class);
};
