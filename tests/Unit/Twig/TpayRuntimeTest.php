<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Twig;

use CommerceWeavers\SyliusTpayPlugin\Twig\TpayRuntime;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class TpayRuntimeTest extends TestCase
{
    use ProphecyTrait;

    private CypherInterface|ObjectProphecy $cypher;

    protected function setUp(): void
    {
        $this->cypher = $this->prophesize(CypherInterface::class);
    }

    public function test_it_converts_minor_to_major_currency(): void
    {
        $result = $this->createTestSubject()->convertMinorToMajorCurrency(100);

        $this->assertEquals(1.0, $result);

        $result = $this->createTestSubject()->convertMinorToMajorCurrency(1000, 3);

        $this->assertEquals(1.0, $result);
    }

    public function test_it_returns_an_unencrypted_config_value(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->willImplement(CryptedInterface::class);
        $gatewayConfig->decrypt($this->cypher)->shouldBeCalled();
        $gatewayConfig->getConfig()->willReturn(['key' => 'decrypted_value']);

        $result = $this->createTestSubject()->getConfigValue($gatewayConfig->reveal(), 'key');

        $this->assertEquals('decrypted_value', $result);
    }

    public function test_it_does_not_decrypt_not_crypted_config(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['key' => 'decrypted_value']);

        $result = $this->createTestSubject()->getConfigValue($gatewayConfig->reveal(), 'key');

        $this->assertEquals('decrypted_value', $result);
    }

    public function test_it_returns_null_when_key_does_not_exist(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['key' => 'decrypted_value']);

        $result = $this->createTestSubject()->getConfigValue($gatewayConfig->reveal(), 'non_existing_key');

        $this->assertNull($result);
    }

    public function test_it_returns_config_value_without_decryption_when_cypher_is_null(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->willImplement(CryptedInterface::class);
        $gatewayConfig->getConfig()->willReturn(['key' => 'encrypted_value']);

        $result = $this->createTestSubjectWithNullCypher()->getConfigValue($gatewayConfig->reveal(), 'key');

        $this->assertEquals('encrypted_value', $result);
    }

    private function createTestSubject(): TpayRuntime
    {
        return new TpayRuntime($this->cypher->reveal());
    }

    private function createTestSubjectWithNullCypher(): TpayRuntime
    {
        return new TpayRuntime(null);
    }
}
