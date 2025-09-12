<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Extension;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\TpayPaymentDetailsType;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint\RequiresTpayChannelId;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\CompleteType;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
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

        $this->addType($builder);
        $this->addModelTransformer($builder);
    }

    private function addType(FormBuilderInterface $builder): void
    {
        $builder
            ->add(
                'tpay',
                TpayPaymentDetailsType::class,
                [
                    'error_bubbling' => false,
                    'property_path' => 'last_cart_payment.details[tpay]',
                    'constraints' => [
                        new Valid(groups: ['sylius_checkout_complete']),
                        new RequiresTpayChannelId(groups: ['sylius_checkout_complete']),
                    ],
                    'validation_groups' => static function (FormInterface $form) {
                        $order = $form->getRoot()->getData();

                        assert($order instanceof OrderInterface);
                        assert($order instanceof OrderLastNewPaymentAwareInterface);

                        $method = $order->getLastPayment()?->getMethod()?->getGatewayConfig()?->getFactoryName();

                        return ['sylius_checkout_complete', $method];
                    },
                ],
            );
    }

    private function addModelTransformer(FormBuilderInterface $builder): void
    {
        $builder->get('tpay')->get('card')->addModelTransformer(
            new CallbackTransformer(
                function (mixed $value): null {
                    return null;
                },
                function (mixed $value): string {
                    if (!\is_array($value)) {
                        return '';
                    }

                    return (string) ($value['card'] ?? '');
                },
            ),
        );
    }

    public static function getExtendedTypes(): iterable
    {
        return [CompleteType::class];
    }
}
