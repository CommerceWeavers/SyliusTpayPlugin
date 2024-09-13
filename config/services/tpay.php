<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateBlik0PaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateBlik0PaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateCardPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateCardPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_tpay.tpay.factory.create_blik0_payment_payload', CreateBlik0PaymentPayloadFactory::class)
        ->args([
            service('commerce_weavers_tpay.tpay.factory.create_redirect_based_payment_payload'),
        ])
        ->alias(CreateBlik0PaymentPayloadFactoryInterface::class, 'commerce_weavers_tpay.factory.create_blik0_payment_payload')
    ;

    $services->set('commerce_weavers_tpay.tpay.factory.create_card_payment_payload', CreateCardPaymentPayloadFactory::class)
        ->args([
            service('commerce_weavers_tpay.tpay.factory.create_redirect_based_payment_payload'),
        ])
        ->alias(CreateCardPaymentPayloadFactoryInterface::class, 'commerce_weavers_tpay.factory.create_card_payment_payload')
    ;

    $services->set('commerce_weavers_tpay.tpay.factory.create_redirect_based_payment_payload', CreateRedirectBasedPaymentPayloadFactory::class)
        ->args([
            service('router'),
            param('commerce_weavers_tpay.payum.create_transaction.success_route'),
            param('commerce_weavers_tpay.payum.create_transaction.error_route'),
        ])
        ->alias(CreateRedirectBasedPaymentPayloadFactoryInterface::class, 'commerce_weavers_tpay.factory.create_redirect_based_payment_payload')
    ;
};
