<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\AbstractTpayGatewayConfigurationType;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayChannelId;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class GatewayConfigurationType extends AbstractTpayGatewayConfigurationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'cards_api',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(groups: ['sylius']),
                    ],
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.cards_api',
                    'priority' => -250,
                    'validation_groups' => ['sylius'],
                ],
            )
            ->add(
                'tpay_channel_id',
                HiddenType::class,
                [
                    'data' => TpayChannelId::CARD,
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_channel_id',
                    'validation_groups' => ['sylius'],
                    'help' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_channel_id_help',
                    'required' => false,
                ],
            )
        ;
    }
}
