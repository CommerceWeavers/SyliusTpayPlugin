<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Entity\PaymentMethodImage;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\AddTpayImageFieldsListener;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\RemoveUnnecessaryPaymentDetailsFieldsListener;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\SetTpayDefaultPaymentImageUrlListener;
use CommerceWeavers\SyliusTpayPlugin\Form\Extension\CheckoutSelectPaymentTypeExtension;
use CommerceWeavers\SyliusTpayPlugin\Form\Extension\CompleteTypeExtension;
use CommerceWeavers\SyliusTpayPlugin\Form\Extension\PaymentMethodTypeExtension;
use CommerceWeavers\SyliusTpayPlugin\Form\Extension\SelectPaymentTypeExtension;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\CheckoutPaymentType;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\RetryPaymentType;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\TpayRetryPaymentDetailsType;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\PaymentMethodImageType;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\TpayGatewayConfigurationType;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\CheckoutTpayPaymentDetailsType;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\TpayGatewayFactory;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CheckoutSelectPaymentTypeExtension::class)
        ->tag('form.type_extension')
    ;

    $services->set(SelectPaymentTypeExtension::class)
        ->tag('form.type_extension')
    ;

    $services->set(CompleteTypeExtension::class)
        ->tag('form.type_extension')
    ;

    $services->set(PaymentMethodTypeExtension::class)
        ->args([
            service('commerce_weavers_sylius_tpay.form.event_listener.add_tpay_image_fields'),
            service('commerce_weavers_sylius_tpay.form.event_listener.set_payment_default_image_url'),
        ])
        ->tag('form.type_extension')
    ;

    $services->set(CheckoutPaymentType::class)
        ->args([
            '%sylius.model.payment.class%',
            '%sylius.form.type.checkout_payment.validation_groups%'
        ])
        ->tag('form.type')
    ;

    $services->set(RetryPaymentType::class)
        ->args([
            '%sylius.model.payment.class%',
            '%sylius.form.type.checkout_payment.validation_groups%'
        ])
        ->tag('form.type')
    ;

    $services->set(TpayGatewayConfigurationType::class)
        ->tag(
            'sylius.gateway_configuration_type',
            ['label' => 'commerce_weavers_sylius_tpay.admin.name', 'type' => TpayGatewayFactory::NAME],
        )
        ->tag('form.type')
    ;

    $services->set(CheckoutTpayPaymentDetailsType::class)
        ->args([
            service('commerce_weavers_sylius_tpay.form.event_listener.remove_unnecessary_payment_details_fields'),
            service('security.token_storage'),
        ])
        ->tag('form.type')
    ;

    $services->set(TpayRetryPaymentDetailsType::class)
        ->args([
            service('security.token_storage'),
        ])
        ->tag('form.type')
    ;

    $services->set(PaymentMethodImageType::class)
        ->args([
            PaymentMethodImage::class,
        ])
        ->tag('form.type')
    ;

    $services->set('commerce_weavers_sylius_tpay.form.event_listener.remove_unnecessary_payment_details_fields', RemoveUnnecessaryPaymentDetailsFieldsListener::class);

    $services
        ->set('commerce_weavers_sylius_tpay.form.event_listener.set_payment_default_image_url', SetTpayDefaultPaymentImageUrlListener::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.cache'),
        ])
    ;

    $services
        ->set('commerce_weavers_sylius_tpay.form.event_listener.add_tpay_image_fields', AddTpayImageFieldsListener::class)
    ;
};
