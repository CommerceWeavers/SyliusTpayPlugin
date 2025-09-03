<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Extension;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\RetryPaymentType;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\ChangePaymentMethodType;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\SelectPaymentType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class SelectPaymentTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('payments');

        $builder->add('payments', ChangePaymentMethodType::class, [
            'entry_type' => RetryPaymentType::class,
            'label' => false,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [SelectPaymentType::class];
    }
}
