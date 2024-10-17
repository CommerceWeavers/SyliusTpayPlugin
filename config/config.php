<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Routing;

return static function(ContainerConfigurator $container): void {
    $container->import('config/**/*.php');

    $parameters = $container->parameters();
    $parameters
        ->set('env(TPAY_CLIENT_ID)', '')
        ->set('env(TPAY_CLIENT_SECRET)', '')
        ->set('env(TPAY_CARDS_API)', '')
        ->set('env(TPAY_GOOGLE_MERCHANT_ID)', '')
        ->set('env(TPAY_APPLE_PAY_MERCHANT_ID)', '')
        ->set('env(TPAY_MERCHANT_ID)', '')
        ->set('env(TPAY_NOTIFICATION_SECURITY_CODE)', '')
        ->set('commerce_weavers_sylius_tpay.certificate.cache_ttl_in_seconds', 300)
        ->set('commerce_weavers_sylius_tpay.tpay_transaction_channels.cache_ttl_in_seconds', 300)
        ->set('commerce_weavers_sylius_tpay.waiting_for_payment.refresh_interval', 5)
        ->set('commerce_weavers_sylius_tpay.payum.create_transaction.success_route', Routing::SHOP_THANK_YOU)
        ->set('commerce_weavers_sylius_tpay.payum.create_transaction.failure_route', Routing::SHOP_PAYMENT_FAILED)
        ->set('commerce_weavers_sylius_tpay.payum.create_transaction.notify_route', Routing::WEBHOOK_TPAY_NOTIFICATION)
    ;
};
