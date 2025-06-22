<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\ContextProvider\RegulationsUrlContextProvider;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

//    $services->set(RegulationsUrlContextProvider::class)
//        ->args([
//            service('sylius.context.locale'),
//        ])
//        ->tag('sylius.ui.template_event.context_provider')
//    ;
};
