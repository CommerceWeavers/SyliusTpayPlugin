<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\Provider;

final class AllowedOrderOperationsProvider implements AllowedOrderOperationsProviderInterface
{
    public const SHOP_PAY_OPERATION = 'shop_pay';

    public const SHOP_CANCEL_LAST_PAYMENT_OPERATION = 'shop_cancel_last_payment';

    public const SHOP_INITIALIZE_APPLE_PAY_SESSION_OPERATION = 'shop_initialize_apple_pay_session';

    public function provide(): array
    {
        return [self::SHOP_PAY_OPERATION, self::SHOP_CANCEL_LAST_PAYMENT_OPERATION, self::SHOP_INITIALIZE_APPLE_PAY_SESSION_OPERATION];
    }
}
