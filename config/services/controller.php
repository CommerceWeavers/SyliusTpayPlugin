<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Controller\DisplayPaymentFailedPageAction;
use CommerceWeavers\SyliusTpayPlugin\Controller\DisplayThankYouPageAction;
use CommerceWeavers\SyliusTpayPlugin\Controller\DisplayWaitingForPaymentPage;
use CommerceWeavers\SyliusTpayPlugin\Controller\InitApplePayPaymentAction;
use CommerceWeavers\SyliusTpayPlugin\Controller\TpayNotificationAction;
use CommerceWeavers\SyliusTpayPlugin\Controller\RetryPaymentAction;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(DisplayPaymentFailedPageAction::class)
        ->args([
            service('twig'),
            service('sylius.repository.order'),
        ])
        ->tag('controller.service_arguments')
    ;

    $services->set(DisplayThankYouPageAction::class)
        ->args([
            service('twig'),
            service('sylius.repository.order'),
        ])
        ->tag('controller.service_arguments')
    ;

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

    $services->set(InitApplePayPaymentAction::class)
        ->args([
            service('payum'),
        ])
        ->tag('controller.service_arguments')
    ;

    $services->set(TpayNotificationAction::class)
        ->args([
            service('payum'),
            service('commerce_weavers_sylius_tpay.payum.factory.notify'),
            service('commerce_weavers_sylius_tpay.payum.factory.notify_data'),
        ])
        ->tag('controller.service_arguments')
    ;

    $services->set(RetryPaymentAction::class)
        ->args([
            service('security.csrf.token_manager'),
            service('sylius.command_bus'),
            service('sylius.repository.order'),
            service('router'),
            service('request_stack'),
        ])
        ->tag('controller.service_arguments')
    ;
};
