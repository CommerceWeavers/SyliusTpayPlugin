<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Serializer\Normalizer\BlikAliasAmbiguousValueErrorNormalizer;
use CommerceWeavers\SyliusTpayPlugin\Api\Serializer\Normalizer\ErrorNormalizer;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.api.serializer.normalizer.blik_alias_ambiguous_value_error', BlikAliasAmbiguousValueErrorNormalizer::class)
        ->args([
            service('api_platform.router'),
        ])
        ->tag('serializer.normalizer', ['priority' => 200])
    ;

    $services->set('commerce_weavers_sylius_tpay.api.serializer.normalizer.error', ErrorNormalizer::class)
        ->decorate('api_platform.jsonld.normalizer.error')
        ->args([
            service('api_platform.router'),
            service('.inner'),
        ])
    ;
};
