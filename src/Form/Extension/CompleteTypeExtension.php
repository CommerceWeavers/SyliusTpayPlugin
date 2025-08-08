<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Extension;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\TpayPaymentDetailsType;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint\RequiresTpayChannelId;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\CompleteType;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

final class CompleteTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var OrderInterface $order */
        $order = $options['data'];

        if (null === $order->getLastPayment()) {
            return;
        }

        $builder
            ->add(
                'tpay',
                TpayPaymentDetailsType::class,
                [
                    'constraints' => [
                        new Valid(groups: ['sylius_checkout_complete']),
                        new RequiresTpayChannelId(groups: ['sylius_checkout_complete']),
                    ],
                    'error_bubbling' => false,
                    'property_path' => 'last_cart_payment.details[tpay]',
                    'validation_groups' => ['sylius_checkout_complete'],
                ],
            )
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [CompleteType::class];
    }
}
