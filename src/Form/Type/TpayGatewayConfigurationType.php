<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\EncryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\PreventSavingEmptyClientSecretListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
        private PreventSavingEmptyClientSecretListener $preventSavingEmptyClientSecretListener,
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
                ],
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.label',
                    'choices' => [
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.redirect' => 'redirect',
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.card' => 'card',
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.blik' => 'blik',
                        'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.pay_by_link' => 'pay-by-link',
                    ],
                ],
            )
            ->add(
                'production_mode',
                CheckboxType::class,
                [
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.production_mode',
                    'value' => false,
                ],
            )
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $this->decryptGatewayConfigListener);
        $builder->addEventListener(FormEvents::POST_SUBMIT, $this->encryptGatewayConfigListener);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $this->preventSavingEmptyClientSecretListener);
    }
}
