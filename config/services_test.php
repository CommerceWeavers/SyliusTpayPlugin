<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Test\Payum\Cypher\FakeCypher;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('payum.dynamic_gateways.cypher', FakeCypher::class)
        ->args([
            env('PAYUM_CYPHER_KEY'),
        ])
    ;
};
