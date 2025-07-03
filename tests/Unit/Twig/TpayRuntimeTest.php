<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Twig;

use CommerceWeavers\SyliusTpayPlugin\Twig\TpayRuntime;
use Payum\Core\Model\GatewayConfigInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class TpayRuntimeTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_converts_minor_to_major_currency(): void
    {
        $result = $this->createTestSubject()->convertMinorToMajorCurrency(100);

        $this->assertEquals(1.0, $result);

        $result = $this->createTestSubject()->convertMinorToMajorCurrency(1000, 3);

        $this->assertEquals(1.0, $result);
    }

    public function test_it_returns_config_value(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['key' => 'value']);

        $result = $this->createTestSubject()->getConfigValue($gatewayConfig->reveal(), 'key');

        $this->assertEquals('value', $result);
    }

    public function test_it_returns_null_when_key_does_not_exist(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['key' => 'value']);

        $result = $this->createTestSubject()->getConfigValue($gatewayConfig->reveal(), 'non_existing_key');

        $this->assertNull($result);
    }

    private function createTestSubject(): TpayRuntime
    {
        return new TpayRuntime();
    }
}
