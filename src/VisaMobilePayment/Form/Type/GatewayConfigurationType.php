<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\VisaMobilePayment\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\AbstractTpayGatewayConfigurationType;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayChannelId;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

final class GatewayConfigurationType extends AbstractTpayGatewayConfigurationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'tpay_channel_id',
            HiddenType::class,
            [
                'data' => TpayChannelId::VISA_MOBILE,
                'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_channel_id',
                'validation_groups' => ['sylius'],
                'help' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_channel_id_help',
                'required' => false,
            ],
        );
    }
}
