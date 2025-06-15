<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\RedirectPayment\Twig\Component\GatewayConfigurationComponent;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.redirect_payment.twig.component.gateway_configuration', GatewayConfigurationComponent::class)
        ->args([
            service('form.factory'),
        ])
        ->tag(
            'sylius.live_component.admin',
            [
                'key' => 'cw_tpay_admin:redirect_payment:gateway_configuration',
                'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/tpay_redirect_gateway_configuration.html.twig',
            ],
        )
    ;
};
