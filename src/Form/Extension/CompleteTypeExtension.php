<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Extension;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\TpayPaymentDetailsType;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\CompleteType;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Valid;

final class CompleteTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly array $validationGroups,
        private readonly DataTransformerInterface $cardDataTransformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var OrderInterface $order */
        $order = $options['data'];

        if (null === $order->getLastPayment()) {
            return;
        }

        $this->addType($builder, $this->validationGroups);
        $this->addModelTransformer($builder);
    }

    private function addType(FormBuilderInterface $builder, array $validationGroups): void
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
                    ],
                    'validation_groups' => static function (FormInterface $form) use ($validationGroups): array {
                        $order = $form->getRoot()->getData();

                        assert($order instanceof OrderInterface);
                        assert($order instanceof OrderLastNewPaymentAwareInterface);

                        $method = $order->getLastPayment()?->getMethod()?->getGatewayConfig()?->getFactoryName();

                        return array_unique(array_merge($validationGroups, [$method]));
                    },
                ],
            );
    }

    private function addModelTransformer(FormBuilderInterface $builder): void
    {
        $builder = $builder->get('tpay');

        if ($builder->has('card')) {
            $builder->get('card')->addModelTransformer($this->cardDataTransformer);
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [CompleteType::class];
    }
}
