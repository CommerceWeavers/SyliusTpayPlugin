<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Payum\Factory\GatewayFactory as BlikGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\Type\TpayCardType;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\Type\TpayCreditCardChoiceType;
use CommerceWeavers\SyliusTpayPlugin\GooglePayPayment\Payum\Factory\GatewayFactory as GooglePayGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\GooglePayPayment\Validator\Constraint\EncodedGooglePayToken;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Factory\GatewayFactory as PBLGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint\ValidTpayChannel;
use CommerceWeavers\SyliusTpayPlugin\VisaMobilePayment\Payum\Factory\GatewayFactory as VisaMobileGatewayFactory;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

final class TpayRetryPaymentDetailsType extends AbstractType
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
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
                    'validation_groups' => function (FormInterface $form): array {
                        return [$this->getValidationGroup($form)];
                    },
                    'constraints' => [
                        new NotBlank(groups: ['payment_retry_' . BlikGatewayFactory::NAME]),
                        new Length(exactly: 6, groups: ['payment_retry_' . BlikGatewayFactory::NAME]),
                    ],
                ],
            )
            ->add(
                'google_pay_token',
                HiddenType::class,
                [
                    'property_path' => '[google_pay_token]',
                    'label' => false,
                    'validation_groups' => function (FormInterface $form): array {
                        return [$this->getValidationGroup($form)];
                    },
                    'constraints' => [
                        new EncodedGooglePayToken(groups: ['payment_retry_', GooglePayGatewayFactory::NAME]),
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
                    'error_bubbling' => false,
                    'property_path' => '[tpay_channel_id]',
                    'validation_groups' => function (FormInterface $form): array {
                        return [$this->getValidationGroup($form)];
                    },
                    'constraints' => [
                        new ValidTpayChannel(groups: ['payment_retry_' . PBLGatewayFactory::NAME]),
                    ],
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
                    'validation_groups' => function (FormInterface $form): array {
                        return [$this->getValidationGroup($form)];
                    },
                    'constraints' => [
                        new Length(min: 7, max: 15, groups: ['payment_retry_' . VisaMobileGatewayFactory::NAME]),
                        new Regex(
                            '/^\d+$/',
                            message: 'commerce_weavers_sylius_tpay.shop.pay.visa_mobile.regex',
                            groups: ['payment_retry_' . VisaMobileGatewayFactory::NAME],
                        ),
                        new NotBlank(groups: ['payment_retry_' . VisaMobileGatewayFactory::NAME]),
                    ],
                    'label' => 'sylius.ui.phone_number',
                ],
            );

        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();
        if ($user instanceof ShopUserInterface) {
            $builder
                ->add(
                    'save_credit_card_for_later',
                    CheckboxType::class,
                    [
                        'label' => 'commerce_weavers_sylius_tpay.shop.order_summary.card.save_credit_card_for_later.label',
                    ],
                );

            $builder
                ->add(
                    'use_saved_credit_card',
                    TpayCreditCardChoiceType::class,
                );
        }
    }

    private function getValidationGroup(FormInterface $form): string
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $form->getParent()?->getParent()?->get('method')->getData();
        if (null === $paymentMethod) {
            return '';
        }

        return \sprintf(
            'payment_retry_%s',
            $paymentMethod->getGatewayConfig()?->getFactoryName(),
        );
    }
}
