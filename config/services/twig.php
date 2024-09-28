<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Twig\TpayExtension;
use CommerceWeavers\SyliusTpayPlugin\Twig\TpayRuntime;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(TpayExtension::class)->tag('twig.extension');

    $services->set(TpayRuntime::class)->args([
        service('payum.dynamic_gateways.cypher'),
    ])->tag('twig.runtime');
};
