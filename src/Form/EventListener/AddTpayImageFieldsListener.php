<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\EventListener;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\PaymentMethodImageType;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class AddTpayImageFieldsListener
{
    public function __invoke(PreSetDataEvent $event): void
    {
        $paymentMethod = $event->getData();
        $form = $event->getForm();

        if (null === $paymentMethod) {
            return;
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (null === $gatewayConfig) {
            return;
        }

        $factoryName = $gatewayConfig->getFactoryName();
        if (null === $factoryName || !str_starts_with($factoryName, 'tpay_')) {
            return;
        }

        $form->add('defaultImageUrl', TextType::class, [
            'label' => 'commerce_weavers_sylius_tpay.admin.form.payment_method.default_image_url',
            'required' => false,
            'attr' => ['readonly' => true],
        ]);

        $form->add('image', PaymentMethodImageType::class, [
            'label' => 'commerce_weavers_sylius_tpay.admin.form.payment_method.image',
            'required' => false,
        ]);
    }
}
