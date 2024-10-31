<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Validator\Constraint\EncodedGooglePayToken;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

final class TpayPaymentDetailsType extends AbstractType
{
    public function __construct(
        private readonly object $removeUnnecessaryPaymentDetailsFieldsListener,
        private readonly object $addSavedCreditCardsListener,
        private readonly TokenStorageInterface $tokenStorage,
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
                    'data' => null,
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
                'apple_pay_token',
                HiddenType::class,
                [
                    'property_path' => '[apple_pay_token]',
                    'label' => false,
                    'validation_groups' => ['sylius_checkout_complete'],
                ],
            )
            ->add(
                'tpay_channel_id',
                HiddenType::class,
                [
                    'property_path' => '[tpay_channel_id]',
                ],
            )
            ->add(
                'visa_mobile_phone_number',
                TelType::class,
                [
                    'property_path' => '[visa_mobile_phone_number]',
                    'attr' => [
                        'placeholder' => 'commerce_weavers_sylius_tpay.shop.order_summary.visa_mobile.placeholder',
                        'maxLength' => 15,
                    ],
                    'validation_groups' => ['sylius_checkout_complete'],
                    'constraints' => [
                        new Length(min: 7, max: 15, groups: ['sylius_checkout_complete']),
                        new Regex(
                            '/^\d+$/',
                            message: 'commerce_weavers_sylius_tpay.shop.pay.visa_mobile.regex',
                            groups: ['sylius_checkout_complete'],
                        ),
                    ],
                    'label' => 'sylius.ui.phone_number',
                ],
            );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this->removeUnnecessaryPaymentDetailsFieldsListener, '__invoke'],
        );

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            [$this->addSavedCreditCardsListener, '__invoke'],
        );

        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if ($user instanceof ShopUserInterface) {
            $builder
                ->add('saveCreditCardForLater', CheckboxType::class,
                    [
                        'label' => 'commerce_weavers_sylius_tpay.shop.order_summary.card.save_credit_card_for_later.label',
                    ]
                )
            ;
        }
    }
}
