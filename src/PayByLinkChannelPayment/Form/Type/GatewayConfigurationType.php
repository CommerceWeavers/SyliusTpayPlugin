<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\EncryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\AbstractTpayGatewayConfigurationType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
                TextType::class,
                [
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_channel_id',
                    'validation_groups' => ['sylius'],
                    'constraints' => [
                        new NotBlank(allowNull: false, groups: ['sylius']),
                    ],
                    'help' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.tpay_channel_id_help',
                    'required' => false,
                ],
            )
        ;
    }
}
