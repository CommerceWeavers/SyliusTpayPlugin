<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class TpayChannelIdEligibility extends Constraint
{
    public const TPAY_CHANNEL_ID_AVAILABLE_ERROR = 'f2a42e4d-21e4-4728-a745-b49d1bf12138';

    public string $availableMessage = 'commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.available';

    public string $notABankMessage = 'commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.not_a_bank';

    public string $doesNotExistMessage = 'commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.does_not_exist';

    /**
     * @return array<string>
     */
    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
