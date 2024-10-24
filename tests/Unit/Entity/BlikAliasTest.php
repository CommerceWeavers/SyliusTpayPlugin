<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Entity;

use CommerceWeavers\SyliusTpayPlugin\Entity\BlikAlias;
use PHPUnit\Framework\TestCase;

final class BlikAliasTest extends TestCase
{
    public function test_it_registers_blik_alias(): void
    {
        $blikAlias = new BlikAlias();

        $blikAlias->register($dateTime = new \DateTimeImmutable('2021-01-01'));

        $this->assertTrue($blikAlias->isRegistered());
        $this->assertSame($dateTime, $blikAlias->getExpirationDate());
    }

    public function test_it_unregisters_blik_alias(): void
    {
        $blikAlias = new BlikAlias();
        $blikAlias->register($dateTime = new \DateTimeImmutable('2021-01-01'));

        $blikAlias->unregister();

        $this->assertFalse($blikAlias->isRegistered());
    }

    public function test_it_redefines_blik_alias(): void
    {
        $blikAlias = new BlikAlias();
        $blikAlias->register(new \DateTimeImmutable('2021-01-01'));

        $blikAlias->redefine();

        $this->assertNotNull($blikAlias->getValue());
        $this->assertFalse($blikAlias->isRegistered());
        $this->assertNull($blikAlias->getExpirationDate());
    }
}
