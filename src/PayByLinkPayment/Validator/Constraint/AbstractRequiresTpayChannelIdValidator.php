<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintValidator;

abstract class AbstractRequiresTpayChannelIdValidator extends ConstraintValidator
{
    protected function getOrder(): ?OrderLastNewPaymentAwareInterface
    {
        $root = $this->context->getRoot();
        if (!$root instanceof FormInterface) {
            return null;
        }

        $order = $root->getData();
        if (!$order instanceof OrderLastNewPaymentAwareInterface) {
            return null;
        }

        return $order;
    }

    protected function isTpayPayByLinkFactory(PaymentInterface $payment): bool
    {
        return $payment->getMethod()?->getGatewayConfig()?->getFactoryName() === 'tpay_pbl';
    }

    protected function validateChannelId(mixed $value, AbstractTpayChannelIdConstraint $constraint): void
    {
        $channelId = $value['tpay_channel_id'] ?? null;
        if ($channelId === null || $channelId === '') {
            $this->context->buildViolation($constraint->message)
                ->atPath('[tpay_channel_id]')
                ->addViolation()
            ;
        }
    }
}
