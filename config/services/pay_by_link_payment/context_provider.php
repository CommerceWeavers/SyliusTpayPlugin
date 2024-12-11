<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\ContextProvider\BankListContextProvider;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.pay_by_link_payment.context_provider.bank_list', BankListContextProvider::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.provider.validated_tpay_api_bank_list'),
            service('commerce_weavers_sylius_tpay.tpay.provider.order_aware_validated_tpay_api_bank_list'),
            service('payum.dynamic_gateways.cypher'),
        ])
        ->tag('sylius.ui.template_event.context_provider')
    ;
};
