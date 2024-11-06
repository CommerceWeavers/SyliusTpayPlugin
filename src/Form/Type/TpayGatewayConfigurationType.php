<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\EncryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PaymentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class TpayGatewayConfigurationType extends AbstractType
{
    public function __construct(
        private readonly DecryptGatewayConfigListenerInterface $decryptGatewayConfigListener,
        private readonly EncryptGatewayConfigListenerInterface $encryptGatewayConfigListener,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'client_id',
                TextType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.client_id',
                    'constraints' => [
                        new NotBlank(allowNull: false, groups: ['sylius']),
                    ],
                    'empty_data' => '',
                ],
            )
            ->add(
                'client_secret',
                TextType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.client_secret',
                    'constraints' => [
                        new NotBlank(allowNull: false, groups: ['sylius']),
                    ],
                    'empty_data' => '',
                ],
            )
            ->add(
                'cards_api',
                TextType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.cards_api',
                    'required' => false,
                    'empty_data' => '',
                ],
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.label',
                    'choices' => [
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.redirect' => PaymentType::REDIRECT,
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.card' => PaymentType::CARD,
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.blik' => PaymentType::BLIK,
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.pay_by_link' => PaymentType::PAY_BY_LINK,
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.google_pay' => PaymentType::GOOGLE_PAY,
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.apple_pay' => PaymentType::APPLE_PAY,
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.visa_mobile' => PaymentType::VISA_MOBILE,
                    ],
                ],
            )
            ->add(
                'merchant_id',
                TextType::class,
                [
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.merchant_id',
                ],
            )
            ->add(
                'notification_security_code',
                TextType::class,
                [
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.notification_security_code',
                ],
            )
            ->add(
                'google_merchant_id',
                TextType::class,
                [
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.google_merchant_id',
                    'required' => false,
                ],
            )
            ->add(
                'apple_pay_merchant_id',
                TextType::class,
                [
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.apple_pay_merchant_id',
                    'required' => false,
                ],
            )
            ->add(
                'production_mode',
                ChoiceType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.production_mode',
                    'choices' => [
                        'sylius.ui.yes_label' => true,
                        'sylius.ui.no_label' => false,
                    ],
                ],
            )
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $this->decryptGatewayConfigListener);
        $builder->addEventListener(FormEvents::POST_SUBMIT, $this->encryptGatewayConfigListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('validation_groups', ['sylius']);
    }
}
