<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class RequiresTpayChannelIdValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!\is_array($value)) {
            return;
        }

        Assert::isInstanceOf($constraint, RequiresTpayChannelId::class);

        $root = $this->context->getRoot();
        if (!$root instanceof FormInterface) {
            return;
        }

        $order = $root->getData();
        if (!$order instanceof OrderLastNewPaymentAwareInterface) {
            return;
        }

        $payment = $order->getLastCartPayment();
        if ($payment === null) {
            return;
        }

        $factoryName = $payment->getMethod()?->getGatewayConfig()?->getFactoryName();
        if ($factoryName !== 'tpay_pbl') {
            return;
        }

        $channelId = $value['tpay_channel_id'] ?? null;
        if ($channelId === null || $channelId === '') {
            $this->context->buildViolation($constraint->message)
                ->atPath('[tpay_channel_id]')
                ->addViolation();
        }
    }
}
