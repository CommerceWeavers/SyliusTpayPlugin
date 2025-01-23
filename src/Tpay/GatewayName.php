<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay;

class GatewayName
{
    public const APPLE_PAY = 'tpay_apple_pay';

    public const BLIK = 'tpay_blik';

    public const CARD = 'tpay_card';

    public const GOOGLE_PAY = 'tpay_google_pay';

    public const PAY_BY_LINK = 'tpay_pbl';

    public const PAY_BY_LINK_CHANNEL = 'tpay_pbl_channel';

    public const REDIRECT = 'tpay_redirect';

    public const VISA_MOBILE = 'tpay_visa_mobile';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::APPLE_PAY,
            self::BLIK,
            self::CARD,
            self::GOOGLE_PAY,
            self::PAY_BY_LINK,
            self::PAY_BY_LINK_CHANNEL,
            self::REDIRECT,
            self::VISA_MOBILE,
        ];
    }
}
