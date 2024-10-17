<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Resolver\BlikAliasResolver;
use CommerceWeavers\SyliusTpayPlugin\Resolver\BlikAliasResolverInterface;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.resolver.blik_alias', BlikAliasResolver::class)
        ->args([
            service('commerce_weavers_sylius_tpay.repository.blik_alias')->nullOnInvalid(),
            service('commerce_weavers_sylius_tpay.factory.blik_alias'),
        ])
        ->alias(BlikAliasResolverInterface::class, 'commerce_weavers_sylius_tpay.resolver.blik_alias')
    ;
};
