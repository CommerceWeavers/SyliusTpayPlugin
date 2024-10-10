<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\TpayBankCollectionDataProvider;
use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\TpayBankItemDataProvider;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_tpay.api.data_provider.collection.tpay_bank', TpayBankCollectionDataProvider::class)
        ->args([
            service('commerce_weavers_tpay.tpay.provider.tpay_api_bank_list'),
        ])
        ->tag('api_platform.collection_data_provider')
    ;

    $services->set('commerce_weavers_tpay.api.data_provider.item.tpay_bank', TpayBankItemDataProvider::class)
        ->args([
            service('commerce_weavers_tpay.tpay.provider.tpay_api_bank_list'),
        ])
        ->tag('api_platform.item_data_provider')
    ;
};
