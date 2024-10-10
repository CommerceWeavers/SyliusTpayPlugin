<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\TpayChannelCollectionDataProvider;
use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\TpayChannelItemDataProvider;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.api.data_provider.collection.tpay_channel', TpayChannelCollectionDataProvider::class)
        ->args([
            service('commerce_weavers_tpay.tpay.provider.tpay_api_bank_list'),
        ])
        ->tag('api_platform.collection_data_provider')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.data_provider.item.tpay_channel', TpayChannelItemDataProvider::class)
        ->args([
            service('commerce_weavers_tpay.tpay.provider.tpay_api_bank_list'),
        ])
        ->tag('api_platform.item_data_provider')
    ;
};
