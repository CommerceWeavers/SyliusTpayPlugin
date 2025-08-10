<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\VisaMobilePayment\Payum\Factory\GatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\TpayGatewayFactoryBuilder;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.visa_mobile_payment.payum.factory.gateway', TpayGatewayFactoryBuilder::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.cache'),
            GatewayFactory::class,
        ])
        ->tag('payum.gateway_factory_builder', ['factory' => GatewayFactory::NAME])
    ;
};
