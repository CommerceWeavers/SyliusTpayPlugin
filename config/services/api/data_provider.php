<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\BankCollectionDataProvider;
use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\BankItemDataProvider;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_tpay.api.data_provider.bank_collection', BankCollectionDataProvider::class)
        ->tag('api_platform.collection_data_provider')
    ;

    $services->set('commerce_weavers_tpay.api.data_provider.bank_item', BankItemDataProvider::class)
        ->tag('api_platform.item_data_provider')
    ;
};
