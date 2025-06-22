<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\StateProvider\TpayChannelStateProvider;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.api.state_provider.tpay_channel', TpayChannelStateProvider::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.resolver.cached_tpay_transaction_channel_resolver'),
        ])
        ->tag('api_platform.state_provider')
    ;
};