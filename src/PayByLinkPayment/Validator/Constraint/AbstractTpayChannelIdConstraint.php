<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

abstract class AbstractTpayChannelIdConstraint extends Constraint
{
    public string $message = 'commerce_weavers_sylius_tpay.shop.pay.tpay_channel.required';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
