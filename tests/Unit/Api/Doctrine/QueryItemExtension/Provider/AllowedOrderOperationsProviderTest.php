<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Doctrine\QueryItemExtension\Provider;

use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\Provider\AllowedOrderOperationsProvider;
use PHPUnit\Framework\TestCase;

final class AllowedOrderOperationsProviderTest extends TestCase
{
    public function test_it_provides_allowed_order_operations(): void
    {
        $provider = new AllowedOrderOperationsProvider();

        $this->assertSame(
            [
                AllowedOrderOperationsProvider::SHOP_PAY_OPERATION,
                AllowedOrderOperationsProvider::SHOP_CANCEL_LAST_PAYMENT_OPERATION,
                AllowedOrderOperationsProvider::SHOP_INITIALIZE_APPLE_PAY_SESSION_OPERATION,
            ],
            $provider->provide()
        );
    }
}
