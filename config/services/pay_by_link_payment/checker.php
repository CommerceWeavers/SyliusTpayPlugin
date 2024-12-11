<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Checker\PaymentMethodSupportedForOrderChecker;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Checker\PaymentMethodSupportedForOrderCheckerInterface;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.pay_by_link_payment.checker.payment_method_supported_for_order', PaymentMethodSupportedForOrderChecker::class)
        ->args([
            service('payum.dynamic_gateways.cypher'),
            service('commerce_weavers_sylius_tpay.tpay.provider.order_aware_validated_tpay_api_bank_list'),
        ])
        ->alias(PaymentMethodSupportedForOrderCheckerInterface::class, 'commerce_weavers_sylius_tpay.pay_by_link_payment.checker.payment_method_supported_for_order')
    ;
};
