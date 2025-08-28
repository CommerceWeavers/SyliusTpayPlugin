<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint\RequiresTpayChannelIdOnRetry;
use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

final class RetryPaymentType extends AbstractType
{
    public function __construct(private string $dataClass)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $form = $event->getForm();
            $payment = $event->getData();

            $form->add('method', PaymentMethodChoiceType::class, [
                'label' => 'sylius.form.checkout.payment_method',
                'subject' => $payment,
                'expanded' => true,
            ]);

            $form->add(
                'tpay',
                TpayRetryPaymentDetailsType::class,
                [
                    'property_path' => 'details[tpay]',
                    'constraints' => [
                        new Valid(groups: ['sylius_checkout_complete']),
                        new RequiresTpayChannelIdOnRetry(groups: ['sylius_checkout_complete']),
                    ],
                    'validation_groups' => ['sylius_checkout_complete'],
                ],
            );
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_checkout_payment';
    }
}
