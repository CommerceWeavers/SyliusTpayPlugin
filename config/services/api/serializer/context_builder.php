<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Serializer\ContextBuilder\OrderTokenAwareContextBuilder;
use CommerceWeavers\SyliusTpayPlugin\Api\Serializer\ContextBuilder\PaymentIdAwareContextBuilder;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.api.serializer.context_builder.order_token_aware', OrderTokenAwareContextBuilder::class)
        ->decorate('api_platform.serializer.context_builder')
        ->args([
            service('.inner'),
        ])
    ;

    $services->set('commerce_weavers_sylius_tpay.api.serializer.context_builder.payment_id_aware', PaymentIdAwareContextBuilder::class)
        ->decorate('api_platform.serializer.context_builder')
        ->args([
            service('.inner'),
        ])
    ;
};
