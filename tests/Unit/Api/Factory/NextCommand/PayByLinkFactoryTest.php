<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Factory\NextCommand;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByLink;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnsupportedNextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand\PayByLinkFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class PayByLinkFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_does_not_support_a_command_without_a_tpay_channel_id(): void
    {
        $factory = $this->createTestSubject();

        $this->assertFalse($factory->supports($this->createCommand(), $this->createPayment()));
    }

    public function test_it_does_not_support_a_command_without_a_payment_with_id(): void
    {
        $factory = $this->createTestSubject();

        $this->assertFalse($factory->supports($this->createCommand(tpayChannelId: '1'), new Payment()));
    }

    public function test_it_supports_a_command_with_a_tpay_channel_id(): void
    {
        $factory = $this->createTestSubject();

        $this->assertTrue($factory->supports($this->createCommand(tpayChannelId: '1'), $this->createPayment()));
    }

    public function test_it_throws_an_exception_when_trying_to_create_a_command_with_unsupported_factory(): void
    {
        $this->expectException(UnsupportedNextCommandFactory::class);

        $this->createTestSubject()->create($this->createCommand(tpayChannelId: '1'), new Payment());
    }

    public function test_it_creates_a_pay_by_link_command(): void
    {
        $command = $this->createTestSubject()->create($this->createCommand(tpayChannelId: '1'), $this->createPayment());

        $this->assertInstanceOf(PayByLink::class, $command);
        $this->assertSame('1', $command->tpayChannelId);
    }

    private function createCommand(?string $token = null, ?string $tpayChannelId = null): Pay
    {
        return new Pay(
            $token ?? 'token',
            'https://cw.nonexisting/success',
            'https://cw.nonexisting/failure',
            tpayChannelId: $tpayChannelId,
        );
    }

    private function createPayment(int $id = 1): PaymentInterface
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getId()->willReturn($id);

        return $payment->reveal();
    }

    private function createTestSubject(): NextCommandFactoryInterface
    {
        return new PayByLinkFactory();
    }
}
