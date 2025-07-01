<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\GooglePayPayment\Twig\Hooks\ConfirmOrderButtonHookable;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.google_pay.twig.hook.confirm_order_button_hookable', ConfirmOrderButtonHookable::class)
        ->decorate('sylius_twig_hooks.renderer.hookable')
        ->args([
            service('.inner'),
        ])
    ;
};
