<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Extension;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\SetTpayDefaultPaymentImageUrlListener;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\PaymentMethodImageType;
use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

final class PaymentMethodTypeExtension extends AbstractTypeExtension
{
    public function __construct(private readonly SetTpayDefaultPaymentImageUrlListener $setDefaultPaymentImageUrlListener)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('defaultImageUrl', TextType::class, [
            'label' => 'commerce_weavers_sylius_tpay.admin.form.payment_method.default_image_url',
            'required' => false,
            'attr' => ['readonly' => true],
        ]);

        $builder->add('image', PaymentMethodImageType::class, [
            'label' => 'commerce_weavers_sylius_tpay.admin.form.payment_method.image',
            'required' => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, $this->setDefaultPaymentImageUrlListener);
    }

    public static function getExtendedTypes(): iterable
    {
        return [PaymentMethodType::class];
    }
}
