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

final class TpayGatewayConfigurationType extends AbstractType
{
    public function __construct(
        private DecryptGatewayConfigListenerInterface $decryptGatewayConfigListener,
        private EncryptGatewayConfigListenerInterface $encryptGatewayConfigListener,
        private PreventSavingEmptyPasswordFieldsListener $preventSavingEmptyClientSecretListener,
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
                ],
            )
            ->add(
                'client_secret',
                PasswordType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.client_secret',
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
                    ],
                ],
            )
            ->add(
                'notification_security_code',
                PasswordType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.notification_security_code',
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
}
