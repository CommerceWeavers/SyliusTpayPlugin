<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Validator\Constraint\EncodedGooglePayToken;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;

final class TpayPaymentDetailsType extends AbstractType
{
    public function __construct(
        private readonly object $removeUnnecessaryPaymentDetailsFieldsListener,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'card',
                TpayCardType::class,
                [
                    'property_path' => '[card]',
                ],
            )
            ->add(
                'blik_token',
                TextType::class,
                [
                    'property_path' => '[blik_token]',
                    'label' => 'commerce_weavers_sylius_tpay.shop.order_summary.blik.token',
                    'validation_groups' => ['sylius_checkout_complete'],
                    'constraints' => [
                        new Length(exactly: 6, groups: ['sylius_checkout_complete']),
                    ],
                ],
            )
            ->add(
                'google_pay_token',
                HiddenType::class,
                [
                    'property_path' => '[google_pay_token]',
                    'label' => false,
                    'validation_groups' => ['sylius_checkout_complete'],
                    'constraints' => [
                        new EncodedGooglePayToken(groups: ['sylius_checkout_complete']),
                    ],
                ],
            )
            ->add(
                'pay_by_link_channel_id',
                HiddenType::class,
                [
                    'property_path' => '[pay_by_link_channel_id]',
                ],
            )
        ;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this->removeUnnecessaryPaymentDetailsFieldsListener, '__invoke'],
        );
    }
}
