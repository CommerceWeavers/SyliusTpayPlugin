<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class TpayChannelIdRequired extends Constraint
{
    public const TPAY_CHANNEL_ID_REQUIRED_ERROR = '9378e788-938d-4fff-934e-448afb4ca410';

    public string $message = 'commerce_weavers_sylius_tpay.shop.pay.pay_by_link_channel.required';

    /**
     * @return array<string>
     */
    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
