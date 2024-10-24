<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Factory\NextCommand;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByRedirect;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnsupportedNextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand\PayByRedirectFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use ECSPrefix202408\Illuminate\Contracts\Auth\Access\Gate;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

class PayByRedirectFactoryTest extends TestCase
{
    use ProphecyTrait;

    private CypherInterface|ObjectProphecy $cypher;

    protected function setUp(): void
    {
        $this->cypher = $this->prophesize(CypherInterface::class);

        parent::setUp();
    }

    public function test_it_does_not_support_if_payment_id_is_null(): void
    {
        $factory = $this->createTestSubject();
        $payment = $this->prophesize(PaymentInterface::class);

        $this->assertFalse($factory->supports($this->createNonRedirectCommand(), $payment->reveal()));
    }

    public function test_it_does_not_support_a_payment_without_gateway_config(): void
    {
        $factory = $this->createTestSubject();
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);

        $payment->getId()->willReturn('1');
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn(null);

        $this->assertFalse($factory->supports($this->createNonRedirectCommand(), $payment->reveal()));
    }

    public function test_it_does_not_support_a_command_without_a_payment_with_id(): void
    {
        $factory = $this->createTestSubject();

        $this->assertFalse($factory->supports($this->createCommand(), new Payment()));
    }

    public function test_it_supports_a_redirect_command(): void
    {
        $factory = $this->createTestSubject();

        $this->assertTrue($factory->supports($this->createCommand(), $this->createPayment()));
    }

    public function test_it_throws_an_exception_when_trying_to_create_a_command_with_unsupported_factory(): void
    {
        $this->expectException(UnsupportedNextCommandFactory::class);

        $this->createTestSubject()->create($this->createCommand(), new Payment());
    }

    public function test_it_supports_and_decrypts_gateway_config_if_it_is_instance_of_crypted_interface(): void
    {
        $factory = $this->createTestSubject();
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(CryptedInterface::class);
        $gatewayConfig->willImplement(GatewayConfigInterface::class);

        $payment->getId()->willReturn('1');
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $gatewayConfig->decrypt($this->cypher->reveal())->shouldBeCalled();

        $gatewayConfig->getConfig()->willReturn(['type' => 'redirect']);

        $this->assertTrue($factory->supports($this->createNonRedirectCommand(), $payment->reveal()));
    }

    public function test_it_creates_a_pay_by_redirect_command(): void
    {
        $command = $this->createTestSubject()->create($this->createCommand(), $this->createPayment());

        $this->assertInstanceOf(PayByRedirect::class, $command);
    }

    private function createCommand(?string $token = null): Pay
    {
        return new Pay(
            $token ?? 'token',
            'https://cw.nonexisting/success',
            'https://cw.nonexisting/failure',
        );
    }

    private function createNonRedirectCommand(?string $token = null): Pay
    {
        return new Pay(
            $token ?? 'token',
            'https://cw.nonexisting/success',
            'https://cw.nonexisting/failure',
            tpayChannelId: '1',
        );
    }

    private function createPayment(int $id = 1): PaymentInterface
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getId()->willReturn($id);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn(['type' => 'redirect']);

        return $payment->reveal();
    }

    private function createTestSubject(): NextCommandFactoryInterface
    {
        return new PayByRedirectFactory(
            $this->cypher->reveal(),
        );
    }
}
