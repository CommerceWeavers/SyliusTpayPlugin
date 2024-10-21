<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Payum\Core\Gateway;

return function(ContainerConfigurator $container): void {
    $container->import('services/**/*.php');

    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.gateway', Gateway::class)
        ->factory([service('payum'), 'getGateway'])
        ->args(['tpay'])
    ;
};
