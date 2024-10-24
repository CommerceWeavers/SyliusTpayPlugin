<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\PayumConfig;

return function(PayumConfig $payum): void {
    $payum
        ->dynamicGateways()
        ->encryption()
        ->defuseSecretKey('%env(PAYUM_CYPHER_KEY)%')
    ;

    $payum->storages('%commerce_weavers_sylius_tpay.model.blik_alias.class%', ['doctrine' => 'orm']);
};
