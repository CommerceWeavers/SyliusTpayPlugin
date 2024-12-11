<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Payment\Canceller\PaymentCanceller;
use CommerceWeavers\SyliusTpayPlugin\Payment\Canceller\PaymentCancellerInterface;
use CommerceWeavers\SyliusTpayPlugin\Payment\Checker\PaymentCancellationPossibilityChecker;
use CommerceWeavers\SyliusTpayPlugin\Payment\Checker\PaymentCancellationPossibilityCheckerInterface;
use CommerceWeavers\SyliusTpayPlugin\Payment\Resolver\OrderBasedPaymentMethodsResolver;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.payment.canceller.payment', PaymentCanceller::class)
        ->args([
            service('sylius_abstraction.state_machine')->nullOnInvalid(),
            service('sm.factory'),
        ])
        ->alias(PaymentCancellerInterface::class, 'commerce_weavers_sylius_tpay.payment.canceller.payment')
    ;

    $services->set('commerce_weavers_sylius_tpay.payment.checker.payment_cancellation_possibility', PaymentCancellationPossibilityChecker::class)
        ->args([
            service('sylius_abstraction.state_machine')->nullOnInvalid(),
            service('sm.factory'),
        ])
        ->alias(PaymentCancellationPossibilityCheckerInterface::class, 'commerce_weavers_sylius_tpay.payment.checker.payment_cancellation_possibility')
    ;

    $services->set('commerce_weavers_sylius_tpay.payment.resolver.order_based_payment_methods', OrderBasedPaymentMethodsResolver::class)
        ->args([
            service('sylius.payment_methods_resolver.channel_based'),
            service('commerce_weavers_sylius_tpay.pay_by_link_payment.checker.payment_method_supported_for_order'),
        ])
        ->tag('sylius.payment_method_resolver', ['type' => 'tpay_order_based', 'label' => 'commerce_weavers_sylius_tpay.payment_methods_resolver.order_based', 'priority' => 2])
    ;
};
