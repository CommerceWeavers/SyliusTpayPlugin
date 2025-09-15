<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class RequiresTpayChannelIdValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, RequiresTpayChannelId::class);

        $root = $this->context->getRoot();
        if (!$root instanceof FormInterface) {
            return;
        }

        $order = $root->getData();
        if (!$order instanceof OrderInterface) {
            return;
        }

        $payment = $order->getLastPayment();
        if ($payment === null) {
            return;
        }

        $factoryName = $payment->getMethod()?->getGatewayConfig()?->getFactoryName();
        if ($factoryName !== 'tpay_pbl') {
            return;
        }

        if (0 === (int) $value) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
