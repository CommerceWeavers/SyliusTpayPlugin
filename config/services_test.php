<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\TpayGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\Test\Payum\Cypher\FakeCypher;
use CommerceWeavers\SyliusTpayPlugin\Test\Payum\Factory\TestTpayGatewayFactory;
use Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.payum.factory.tpay_gateway', GatewayFactoryBuilder::class)
        ->args([
            TestTpayGatewayFactory::class,
        ])
        ->tag('payum.gateway_factory_builder', ['factory' => TpayGatewayFactory::NAME])
    ;

    $services->set('payum.dynamic_gateways.cypher', FakeCypher::class)
        ->args([
            env('PAYUM_CYPHER_KEY'),
        ])
    ;
};
