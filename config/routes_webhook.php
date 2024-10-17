<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Controller\InitApplePayPaymentAction;
use CommerceWeavers\SyliusTpayPlugin\Controller\TpayNotificationAction;
use CommerceWeavers\SyliusTpayPlugin\Routing;
use Symfony\Component\HttpFoundation\Request;

return function(RoutingConfigurator $routes): void {
    $routes->add(Routing::INIT_APPLE_PAY_PAYMENT, Routing::INIT_APPLE_PAY_PAYMENT_PATH)
        ->controller(InitApplePayPaymentAction::class)
        ->methods([Request::METHOD_POST])
    ;

    $routes->add(Routing::WEBHOOK_TPAY_NOTIFICATION, Routing::WEBHOOK_TPAY_NOTIFICATION_PATH)
        ->controller(TpayNotificationAction::class)
        ->methods([Request::METHOD_POST])
    ;
};
