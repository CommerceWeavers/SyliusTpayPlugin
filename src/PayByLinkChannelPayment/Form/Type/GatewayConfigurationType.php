<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\EncryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\AbstractTpayGatewayConfigurationType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GatewayConfigurationType extends AbstractTpayGatewayConfigurationType
{
    public function __construct(
        DecryptGatewayConfigListenerInterface $decryptGatewayConfigListener,
        EncryptGatewayConfigListenerInterface $encryptGatewayConfigListener,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct($decryptGatewayConfigListener, $encryptGatewayConfigListener);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'tpay_channel_id',
                TextType::class,
                [
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_channel_id',
                    'help' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_channel_id_help',
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->translator->trans('commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_display_all_channels', domain: 'messages'),
                        'data-display-all-label' => $this->translator->trans('commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_display_all_channels', domain: 'messages'),
                    ],
                ],
            )
        ;
    }
}
