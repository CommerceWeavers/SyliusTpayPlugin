<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class RequiresTpayChannelId extends Constraint
{
    public string $message = 'commerce_weavers_sylius_tpay.shop.pay.tpay_channel.required';

    public function __construct(
        public bool $isRetryPayment = false,
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
