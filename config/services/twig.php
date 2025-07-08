<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Twig\Component\PoliciesComponent;
use CommerceWeavers\SyliusTpayPlugin\Twig\TpayExtension;
use CommerceWeavers\SyliusTpayPlugin\Twig\TpayRuntime;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(TpayExtension::class)->tag('twig.extension');

    $services->set(TpayRuntime::class)
        ->tag('twig.runtime')
    ;

    $services->set('commerce_weavers_sylius_tpay.twig.component.policies', PoliciesComponent::class)
        ->args([
            service('sylius.context.locale'),
        ])
        ->tag('twig.component', [
            'key' => 'cw_tpay_shop:policies',
            'template' => '@CommerceWeaversSyliusTpayPlugin/shop/component/policies.html.twig',
        ])
    ;
};
