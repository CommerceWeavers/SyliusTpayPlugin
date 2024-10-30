<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\EncryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\PreventSavingEmptyPasswordFieldsListener;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PaymentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class TpayGatewayConfigurationType extends AbstractType
{
    public function __construct(
        private readonly DecryptGatewayConfigListenerInterface $decryptGatewayConfigListener,
        private readonly EncryptGatewayConfigListenerInterface $encryptGatewayConfigListener,
        private readonly PreventSavingEmptyPasswordFieldsListener $preventSavingEmptyClientSecretListener,
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
                        new NotBlank(groups: [
                            'commerce_weavers_sylius_tpay:shop:payment_method:default_type',
                        ]),
                    ],
                ],
            )
            ->add(
                'client_secret',
                PasswordType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.client_secret',
//                    'constraints' => [
//                        new NotBlank(groups: [
//                            'commerce_weavers_sylius_tpay:shop:payment_method:default_type',
//                        ]),
//                    ],
                ],
            )
            ->add(
                'cards_api',
                TextType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.cards_api',
                    'required' => false,
                    'empty_data' => '',
                    'constraints' => [
                        new NotBlank(groups: ['commerce_weavers_sylius_tpay:shop:payment_method:card_type']),
                    ],
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
                    ],
                ],
            )
            ->add(
                'merchant_id',
                TextType::class,
                [
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.merchant_id',
                    'constraints' => [
                        new NotBlank(groups: [
                            'commerce_weavers_sylius_tpay:shop:payment_method:default_type',
                        ]),
                    ],
                ],
            )
            ->add(
                'google_merchant_id',
                TextType::class,
                [
                    'empty_data' => '',
                    'required' => false,
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.google_merchant_id',
                    'constraints' => [
                        new NotBlank(groups: ['commerce_weavers_sylius_tpay:shop:payment_method:google_pay_type']),
                    ],
                ],
            )
            ->add(
                'apple_pay_merchant_id',
                TextType::class,
                [
                    'empty_data' => '',
                    'required' => false,
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.apple_pay_merchant_id',
                    'constraints' => [
                        new NotBlank(groups: [
                            'commerce_weavers_sylius_tpay:shop:payment_method:apple_pay_type',
                        ]),
                    ],
                ],
            )
            ->add(
                'notification_security_code',
                PasswordType::class,
                [
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.notification_security_code',
                    'constraints' => [
                        new NotNull(groups: [
                            'commerce_weavers_sylius_tpay:shop:payment_method:default_type',
                        ]),
                    ],
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
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $this->preventSavingEmptyClientSecretListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'validation_groups' => function (FormInterface $form) {
                /** @var array $data */
                $data = $form->getData();

                $defaultType = 'commerce_weavers_sylius_tpay:shop:payment_method:default_type';

                $result = match($data['type']) {
                    PaymentType::CARD => 'commerce_weavers_sylius_tpay:shop:payment_method:card_type',
                    PaymentType::GOOGLE_PAY => 'commerce_weavers_sylius_tpay:shop:payment_method:google_pay_type',
                    PaymentType::APPLE_PAY => 'commerce_weavers_sylius_tpay:shop:payment_method:apple_pay_type',
                    default => $defaultType,
                };

                return $result === $defaultType ? [$result] : [$result, $defaultType];
            },
        ));
    }
}
