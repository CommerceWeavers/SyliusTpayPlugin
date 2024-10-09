<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class PayByLinkChannelIdAvailable extends Constraint
{
    public const PAY_BY_LINK_CHANNEL_ID_AVAILABLE_ERROR = 'f2a42e4d-21e4-4728-a745-b49d1bf12138';

    public string $message = 'commerce_weavers_sylius_tpay.shop.pay.pay_by_link_channel.available';

    /**
     * @return array<string>
     */
    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
