<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\TpayChannelCollectionDataProvider;
use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\TpayChannelItemDataProvider;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.api.data_provider.collection.tpay_channel', TpayChannelCollectionDataProvider::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.resolver.cached_tpay_transaction_channel_resolver'),
        ])
        ->tag('api_platform.collection_data_provider')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.data_provider.item.tpay_channel', TpayChannelItemDataProvider::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.resolver.cached_tpay_transaction_channel_resolver'),
        ])
        ->tag('api_platform.item_data_provider')
    ;
};
