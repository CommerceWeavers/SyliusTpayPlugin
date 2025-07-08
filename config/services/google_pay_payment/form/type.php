<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use CommerceWeavers\SyliusTpayPlugin\GooglePayPayment\Form\Type\GatewayConfigurationType;
use CommerceWeavers\SyliusTpayPlugin\GooglePayPayment\Payum\Factory\GatewayFactory;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.google_pay_payment.form.type.gateway_configuration', GatewayConfigurationType::class)
        ->parent('commerce_weavers_sylius_tpay.form.type.abstract_tpay_gateway_configuration')
        ->tag('sylius.gateway_configuration_type', ['label' => 'commerce_weavers_sylius_tpay.admin.gateway_name.tpay_google_pay', 'type' => GatewayFactory::NAME])
        ->tag('form.type')
    ;
};
