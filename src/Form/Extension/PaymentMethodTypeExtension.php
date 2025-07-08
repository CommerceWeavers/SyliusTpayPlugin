<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Extension;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\AddTpayImageFieldsListener;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\SetTpayDefaultPaymentImageUrlListener;
use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

final class PaymentMethodTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly AddTpayImageFieldsListener $addTpayImageFieldsListener,
        private readonly SetTpayDefaultPaymentImageUrlListener $setDefaultPaymentImageUrlListener,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $this->addTpayImageFieldsListener);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $this->setDefaultPaymentImageUrlListener);
    }

    public static function getExtendedTypes(): iterable
    {
        return [PaymentMethodType::class];
    }
}
