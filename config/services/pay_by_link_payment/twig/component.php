<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Form\Type\GatewayConfigurationType;
use CommerceWeavers\SyliusTpayPlugin\Twig\Component\GatewayConfigurationComponent;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.pay_by_link.twig.component.gateway_configuration', GatewayConfigurationComponent::class)
        ->args([
            GatewayConfigurationType::class,
            service('form.factory'),
        ])
        ->tag(
            'sylius.live_component.admin',
            [
                'key' => 'cw_tpay_admin:pay_by_link:gateway_configuration',
                'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/gateway_configuration.html.twig',
            ],
        )
    ;
};
