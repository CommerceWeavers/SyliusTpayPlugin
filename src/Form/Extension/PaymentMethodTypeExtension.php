<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Extension;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\PaymentMethodImageType;
use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class PaymentMethodTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('image', PaymentMethodImageType::class, [
            'label' => 'sylius.form.payment_method.image',
            'required' => false,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [PaymentMethodType::class];
    }
}
