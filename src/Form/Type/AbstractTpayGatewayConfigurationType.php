<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

abstract class AbstractTpayGatewayConfigurationType extends AbstractType
{
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
                    'constraints' => [
                        new NotBlank(allowNull: false, groups: ['sylius']),
                    ],
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.client_id',
                    'priority' => 0,
                    'validation_groups' => ['sylius'],
                ],
            )
            ->add(
                'client_secret',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(allowNull: false, groups: ['sylius']),
                    ],
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.client_secret',
                    'priority' => -100,
                    'validation_groups' => ['sylius'],
                ],
            )
            ->add(
                'merchant_id',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(allowNull: false, groups: ['sylius']),
                    ],
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.merchant_id',
                    'priority' => -200,
                    'validation_groups' => ['sylius'],
                ],
            )
            ->add(
                'notification_security_code',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(allowNull: false, groups: ['sylius']),
                    ],
                    'empty_data' => '',
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.notification_security_code',
                    'priority' => -300,
                    'validation_groups' => ['sylius'],
                ],
            )
            ->add(
                'production_mode',
                ChoiceType::class,
                [
                    'choices' => [
                        'sylius.ui.yes_label' => true,
                        'sylius.ui.no_label' => false,
                    ],
                    'label' => 'commerce_weavers_sylius_tpay.admin.gateway_configuration.production_mode',
                    'priority' => -400,
                ],
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('validation_groups', ['sylius']);
    }
}
