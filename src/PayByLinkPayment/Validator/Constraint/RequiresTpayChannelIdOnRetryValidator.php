<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Webmozart\Assert\Assert;

final class RequiresTpayChannelIdOnRetryValidator extends AbstractRequiresTpayChannelIdValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!\is_array($value)) {
            return;
        }

        Assert::isInstanceOf($constraint, RequiresTpayChannelIdOnRetry::class);

        $payment = $this->getOrder()?->getLastNewPayment();
        if ($payment === null || !$this->isTpayPayByLinkFactory($payment)) {
            return;
        }

        $this->validateChannelId($value, $constraint);
    }
}
