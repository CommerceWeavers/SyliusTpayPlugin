<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay;

class PaymentType
{
    public const CARD = 'card';

    public const BLIK = 'blik';

    public const PAY_BY_LINK = 'pay-by-link';

    public const REDIRECT = 'redirect';

    public const GOOGLE_PAY = 'google_pay';

    public const APPLE_PAY = 'apple_pay';

    public const VISA_MOBILE = 'visa_mobile';
}
