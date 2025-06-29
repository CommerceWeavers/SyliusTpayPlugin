<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\EncryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\AbstractTpayGatewayConfigurationType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class GatewayConfigurationType extends AbstractTpayGatewayConfigurationType
{
    public function __construct(
        DecryptGatewayConfigListenerInterface $decryptGatewayConfigListener,
        EncryptGatewayConfigListenerInterface $encryptGatewayConfigListener,
    ) {
        parent::__construct($decryptGatewayConfigListener, $encryptGatewayConfigListener);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'tpay_channel_id',
                ChoiceType::class,
                [
                    'empty_data' => null,
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_channel_id',
                    'validation_groups' => ['sylius'],
                    'constraints' => [
                        new NotBlank(allowNull: false, groups: ['sylius']),
                    ],
                    'placeholder' => 'sylius.ui.select',
                    'help' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_channel_id_help',
                    'required' => false,
                ],
            )
        ;
    }
}
