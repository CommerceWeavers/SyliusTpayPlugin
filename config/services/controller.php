<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Controller\DisplayWaitingForPaymentPage;
use CommerceWeavers\SyliusTpayPlugin\Controller\PaymentNotificationAction;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(DisplayWaitingForPaymentPage::class)
        ->args([
            service('payum'),
            service('router'),
            service('sylius.factory.payum_resolve_next_route'),
            service('twig'),
            param('commerce_weavers_sylius_tpay.waiting_for_payment.refresh_interval'),
        ])
        ->tag('controller.service_arguments')
    ;

    $services->set(PaymentNotificationAction::class)
        ->args([
            service('payum'),
            service('commerce_weavers_sylius_tpay.payum.factory.notify'),
            service('commerce_weavers_sylius_tpay.payum.factory.notify_data'),
        ])
        ->tag('controller.service_arguments')
    ;
};
