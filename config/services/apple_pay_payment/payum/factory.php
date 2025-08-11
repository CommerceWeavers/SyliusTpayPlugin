<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\ApplePayPayment\Payum\Factory\GatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\ApplePayPayment\Payum\Factory\InitializeApplePayPaymentFactory;
use CommerceWeavers\SyliusTpayPlugin\ApplePayPayment\Payum\Factory\InitializeApplePayPaymentFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\TpayGatewayFactoryBuilder;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.apple_pay_payment.payum.factory.gateway', TpayGatewayFactoryBuilder::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.cache'),
            GatewayFactory::class,
        ])
        ->tag('payum.gateway_factory_builder', ['factory' => GatewayFactory::NAME])
    ;

    $services->set('commerce_weavers_sylius_tpay.payum.factory.initialize_apple_pay_payment', InitializeApplePayPaymentFactory::class)
        ->alias(InitializeApplePayPaymentFactoryInterface::class, 'commerce_weavers_sylius_tpay.payum.factory.initialize_apple_pay_payment')
    ;
};
