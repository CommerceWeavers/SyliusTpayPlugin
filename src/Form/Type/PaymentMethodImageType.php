<?php

namespace CommerceWeavers\SyliusTpayPlugin\Form\Type;

use Sylius\Bundle\CoreBundle\Form\Type\ImageType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

final class PaymentMethodImageType extends ImageType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->remove('type');
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_payment_method_image';
    }
}
