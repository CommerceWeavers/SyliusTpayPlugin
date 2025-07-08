<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\Type\GatewayConfigurationType;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\Type\TpayCardType;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\Type\TpayCreditCardChoiceType;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Payum\Factory\GatewayFactory;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.card_payment.form.type.gateway_configuration', GatewayConfigurationType::class)
        ->parent('commerce_weavers_sylius_tpay.form.type.abstract_tpay_gateway_configuration')
        ->tag('sylius.gateway_configuration_type', ['label' => 'commerce_weavers_sylius_tpay.admin.gateway_name.tpay_card', 'type' => GatewayFactory::NAME])
        ->tag('form.type')
    ;

    $services->set('commerce_weavers_sylius_tpay.card_payment.form.type.tpay_card', TpayCardType::class)
        ->args([
            service('commerce_weavers_sylius_tpay.card_payment.form.data_transformer.card_type'),
        ])
        ->tag('form.type')
    ;

    $services->set('commerce_weavers_sylius_tpay.card_payment.form.type.tpay_credit_card_choice', TpayCreditCardChoiceType::class)
        ->args([
            service('security.token_storage'),
            service('translator'),
            service('commerce_weavers_sylius_tpay.repository.credit_card'),
            service('sylius.context.cart'),
        ])
        ->tag('form.type')
    ;
};
