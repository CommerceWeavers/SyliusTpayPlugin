<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Factory\NextCommand;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByBlik;
use CommerceWeavers\SyliusTpayPlugin\Api\Enum\BlikAliasAction;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnsupportedNextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand\PayByBlikFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;

final class PayByBlikFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_does_not_support_a_command_without_a_blik_token(): void
    {
        $factory = $this->createTestSubject();

        $this->assertFalse($factory->supports($this->createCommand(), $this->createPayment()));
    }

    public function test_it_does_not_support_a_command_without_a_payment_with_id(): void
    {
        $factory = $this->createTestSubject();

        $this->assertFalse($factory->supports($this->createCommand(blikToken: '777123'), new Payment()));
    }

    public function test_it_supports_a_command_with_a_blik_token(): void
    {
        $factory = $this->createTestSubject();

        $this->assertTrue($factory->supports($this->createCommand(blikToken: '777123'), $this->createPayment()));
    }

    public function test_it_throws_an_exception_when_trying_to_create_a_command_with_unsupported_factory(): void
    {
        $this->expectException(UnsupportedNextCommandFactory::class);

        $this->createTestSubject()->create($this->createCommand(), new Payment());
    }

    public function test_it_creates_a_pay_by_blik_command(): void
    {
        $command = $this->createTestSubject()->create($this->createCommand(blikToken: '777123'), $this->createPayment());

        $this->assertInstanceOf(PayByBlik::class, $command);
        $this->assertSame('777123', $command->blikToken);
    }

    public function test_it_creates_a_pay_by_blik_command_with_an_alias_action(): void
    {
        $command = $this->createTestSubject()->create($this->createCommand(blikToken: '777123', blikAliasAction: BlikAliasAction::REGISTER), $this->createPayment());

        $this->assertInstanceOf(PayByBlik::class, $command);
        $this->assertSame('777123', $command->blikToken);
        $this->assertSame(BlikAliasAction::REGISTER, $command->blikAliasAction);
    }

    private function createCommand(?string $token = null, ?string $blikToken = null, ?BlikAliasAction $blikAliasAction = null): Pay
    {
        return new Pay(
            $token ?? 'token',
            'https://cw.nonexisting/success',
            'https://cw.nonexisting/failure',
            blikToken: $blikToken,
            blikAliasAction: $blikAliasAction,
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
        return new PayByBlikFactory();
    }
}
