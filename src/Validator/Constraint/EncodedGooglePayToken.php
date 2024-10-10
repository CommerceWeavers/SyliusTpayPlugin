<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class EncodedGooglePayToken extends Constraint
{
    public const NOT_ENCODED_ERROR = 'c146928c-f22b-4802-ba90-5fb9952e7ee8';

    public const NOT_JSON_ENCODED_ERROR = '056d5589-f7b2-497f-86f6-79e8b74ab8be';

    public string $notEncodedErrorMessage = 'commerce_weavers_sylius_tpay.shop.pay.google_pay_token.not_json_encoded';

    public string $notJsonEncodedErrorMessage = 'commerce_weavers_sylius_tpay.shop.pay.google_pay_token.not_json_encoded';

    protected static $errorNames = [
        self::NOT_ENCODED_ERROR => 'NOT_ENCODED_ERROR',
        self::NOT_JSON_ENCODED_ERROR => 'NOT_JSON_ENCODED_ERROR',
    ];

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
