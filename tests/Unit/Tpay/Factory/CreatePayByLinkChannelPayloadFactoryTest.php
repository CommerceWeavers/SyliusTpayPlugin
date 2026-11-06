<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreatePayByLinkChannelPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\Exception\BankNotSelectedException;
use Payum\Core\Security\CypherInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;

final class CreatePayByLinkChannelPayloadFactoryTest extends TestCase
{
    use ProphecyTrait;

    private CreateRedirectBasedPaymentPayloadFactoryInterface|ObjectProphecy $createRedirectBasedPaymentPayloadFactory;

    private CypherInterface|ObjectProphecy $cypher;

    protected function setUp(): void
    {
        $this->cypher = $this->prophesize(CypherInterface::class);
        $this->createRedirectBasedPaymentPayloadFactory = $this->prophesize(CreateRedirectBasedPaymentPayloadFactoryInterface::class);
    }

    public function test_it_throws_exception_if_tpay_channel_id_is_missing(): void
    {
        $this->expectException(BankNotSelectedException::class);
        $this->expectExceptionMessage('The given payment does not have a bank selected.');

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['some_other_key' => 'value']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, 'https://cw.org/notify', 'pl_PL')->willReturn(['pay' => ['some' => 'data']]);

        $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');
    }

    public function test_it_adds_channel_id_to_a_basic_create_payment_payload_output(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['tpay_channel_id' => '123']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, 'https://cw.org/notify', 'pl_PL')->willReturn(['pay' => ['some' => 'data']]);

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');

        $this->assertSame([
            'pay' => [
                'some' => 'data',
                'channelId' => 123,
            ],
        ], $payload);
    }

    public function test_it_adds_channel_id_from_an_encrypted_gateway_config(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig(['tpay_channel_id' => 'encrypted_123', 'encrypted' => true]);

        $this->cypher->decrypt('encrypted_123')->willReturn('123')->shouldBeCalled();

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, 'https://cw.org/notify', 'pl_PL')->willReturn(['pay' => ['some' => 'data']]);

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');

        $this->assertSame([
            'pay' => [
                'some' => 'data',
                'channelId' => 123,
            ],
        ], $payload);
    }

    public function test_it_throws_exception_if_tpay_channel_id_is_missing_in_an_encrypted_gateway_config(): void
    {
        $this->expectException(BankNotSelectedException::class);

        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig(['some_other_key' => 'encrypted_value', 'encrypted' => true]);

        $this->cypher->decrypt('encrypted_value')->willReturn('value');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, 'https://cw.org/notify', 'pl_PL')->willReturn(['pay' => ['some' => 'data']]);

        $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');
    }

    private function createTestSubject(): CreatePayByLinkChannelPayloadFactory
    {
        return new CreatePayByLinkChannelPayloadFactory(
            $this->createRedirectBasedPaymentPayloadFactory->reveal(),
            $this->cypher->reveal(),
        );
    }
}
