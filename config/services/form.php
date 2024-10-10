<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Form\DataTransformer\CardTypeDataTransformer;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListener;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\EncryptGatewayConfigListener;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\PreventSavingEmptyPasswordFieldsListener;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\RemoveUnnecessaryPaymentDetailsFieldsListener;
use CommerceWeavers\SyliusTpayPlugin\Form\Extension\CompleteTypeExtension;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\TpayCardType;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\TpayGatewayConfigurationType;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\TpayPaymentDetailsType;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\TpayGatewayFactory;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CompleteTypeExtension::class)
        ->tag('form.type_extension')
    ;

    $services->set(TpayGatewayConfigurationType::class)
        ->args([
            service('commerce_weavers_sylius_tpay.form.event_listener.decrypt_gateway_config'),
            service('commerce_weavers_sylius_tpay.form.event_listener.encrypt_gateway_config'),
            service('commerce_weavers_sylius_tpay.form.event_listener.prevent_saving_empty_password_fields'),
        ])
        ->tag(
            'sylius.gateway_configuration_type',
            ['label' => 'commerce_weavers_sylius_tpay.admin.name', 'type' => TpayGatewayFactory::NAME],
        )
        ->tag('form.type')
    ;

    $services->set(TpayCardType::class)
        ->args([
            service('commerce_weavers_sylius_tpay.form.data_transformer.card_type'),
        ])
        ->tag('form.type')
    ;

    $services->set(TpayPaymentDetailsType::class)
        ->args([
            service('commerce_weavers_sylius_tpay.form.event_listener.remove_unnecessary_payment_details_fields'),
        ])
        ->tag('form.type')
    ;

    $services->set('commerce_weavers_sylius_tpay.form.data_transformer.card_type', CardTypeDataTransformer::class);

    $services
        ->set('commerce_weavers_sylius_tpay.form.event_listener.decrypt_gateway_config', DecryptGatewayConfigListener::class)
        ->args([
            service('payum.dynamic_gateways.cypher'),
        ])
    ;

    $services
        ->set('commerce_weavers_sylius_tpay.form.event_listener.encrypt_gateway_config', EncryptGatewayConfigListener::class)
        ->args([
            service('payum.dynamic_gateways.cypher'),
        ])
    ;

    $services->set('commerce_weavers_sylius_tpay.form.event_listener.prevent_saving_empty_password_fields', PreventSavingEmptyPasswordFieldsListener::class);

    $services->set('commerce_weavers_sylius_tpay.form.event_listener.remove_unnecessary_payment_details_fields', RemoveUnnecessaryPaymentDetailsFieldsListener::class);
};
