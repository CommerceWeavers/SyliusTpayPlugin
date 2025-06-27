<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Documentation\OpenApi\PayDocumentationModifier;
use CommerceWeavers\SyliusTpayPlugin\Api\Documentation\Swagger\PayDocumentationNormalizer;
use Sylius\Bundle\CoreBundle\SyliusCoreBundle;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    if (SyliusCoreBundle::VERSION_ID >= 11300) {
        $services->set('commerce_weavers_sylius_tpay.api.documentation.open_api.pay_documentation_modifier', PayDocumentationModifier::class)
            ->args([
                param('sylius.security.new_api_shop_route')
            ])
            ->tag('sylius.open_api.modifier')
        ;
    } else {
        $services->set('commerce_weavers_sylius_tpay.api.documentation.swagger.pay_documentation_normalizer', PayDocumentationNormalizer::class)
            ->decorate('api_platform.swagger.normalizer.documentation')
            ->args([
                service('.inner'),
                param('sylius.security.new_api_shop_route'),
            ])
        ;
    }
};
