<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class TpayChannelIdEligibility extends Constraint
{
    public const TPAY_CHANNEL_ID_AVAILABLE_ERROR = 'f2a42e4d-21e4-4728-a745-b49d1bf12138';

    public const TPAY_CHANNEL_ID_NOT_BANK_ERROR = '2ecd8f05-0500-489e-93ca-701167e07768';

    public const TPAY_CHANNEL_ID_DOES_NOT_EXIST_ERROR = 'f51e4f12-121a-4a1f-9cac-9862cf54732f';

    public string $availableMessage = 'commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.not_available';

    public string $notBankMessage = 'commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.not_a_bank';

    public string $doesNotExistMessage = 'commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.does_not_exist';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
