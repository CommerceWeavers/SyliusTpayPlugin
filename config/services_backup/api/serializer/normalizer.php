<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Serializer\Normalizer\BlikAliasAmbiguousValueErrorNormalizer;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.api.serializer.normalizer.blik_alias_ambiguous_value_error', BlikAliasAmbiguousValueErrorNormalizer::class)
        ->args([
            service('api_platform.router'),
        ])
        ->tag('serializer.normalizer', ['priority' => -790])
    ;
};
