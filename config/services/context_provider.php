<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// TODO: TEMPORARY - Commented out for API Platform migration (PR #273)
// RegulationsUrlContextProvider is temporarily disabled due to missing Sylius interface
// This service registration needs to be restored when proper Sylius version is available
/*
use CommerceWeavers\SyliusTpayPlugin\ContextProvider\RegulationsUrlContextProvider;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RegulationsUrlContextProvider::class)
        ->args([
            service('sylius.context.locale'),
        ])
        ->tag('sylius.ui.template_event.context_provider')
    ;
};
*/

return static function(ContainerConfigurator $container): void {
    // Empty service configuration - RegulationsUrlContextProvider temporarily disabled
};
