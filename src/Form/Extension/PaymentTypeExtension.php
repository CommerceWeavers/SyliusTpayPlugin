<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Extension;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\TpayPaymentDetailsType;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\PaymentType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Form\FormInterface;

final class PaymentTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'tpay',
                TpayPaymentDetailsType::class,
                [
                    'property_path' => 'details[tpay]',
                    'validation_groups' => static function (FormInterface $form) {
                        $order = $form->getRoot()->getData();

                        assert($order instanceof OrderInterface);
                        assert($order instanceof OrderLastNewPaymentAwareInterface);

                        $method = $order->getLastPayment('new')?->getMethod()?->getGatewayConfig()?->getFactoryName();

                        return [$method];
                    }
                ]
            );
    }

    public static function getExtendedTypes(): iterable
    {
        return [PaymentType::class];
    }
}
