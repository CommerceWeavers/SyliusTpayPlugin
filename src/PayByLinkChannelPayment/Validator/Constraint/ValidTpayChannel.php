<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class ValidTpayChannel extends Constraint
{
    public string $message = 'commerce_weavers_sylius_tpay.shop.pay.tpay_channel.not_valid';

    public const NOT_VALID_CHANNEL_ERROR = '632f97f3-c302-409b-a321-ec078194302d';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
