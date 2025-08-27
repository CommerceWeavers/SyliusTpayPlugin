<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Extension;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\CheckoutPaymentType;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\ChangePaymentMethodType;
use Sylius\Bundle\ShopBundle\Form\Type\Checkout\SelectPaymentType as CheckoutSelectPaymentType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class CheckoutSelectPaymentTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('payments');

        $builder->add('payments', ChangePaymentMethodType::class, [
            'entry_type' => CheckoutPaymentType::class,
            'label' => false,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [CheckoutSelectPaymentType::class];
    }
}
