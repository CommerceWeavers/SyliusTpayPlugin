<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Factory\NextCommand;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByVisaMobile;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnsupportedNextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand\PayByVisaMobileFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use Sylius\Component\Core\Model\Payment;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\PaymentInterface;

class PayByVisaMobileFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_does_not_support_a_command_without_an_visa_mobile_phone_number(): void
    {
        $factory = $this->createTestSubject();

        $this->assertFalse($factory->supports($this->createCommand(), $this->createPayment()));
    }

    public function test_it_does_not_support_a_command_without_a_payment_with_id(): void
    {
        $factory = $this->createTestSubject();

        $this->assertFalse($factory->supports($this->createCommand(), new Payment()));
    }

    public function test_it_supports_a_command_with_a_visa_mobile_phone_number(): void
    {
        $factory = $this->createTestSubject();

        $this->assertTrue($factory->supports($this->createCommand(visaMobilePhoneNumber: '44123456789'), $this->createPayment()));
    }

    public function test_it_creates_a_pay_by_visa_mobile_command(): void
    {
        $command = $this->createTestSubject()->create($this->createCommand(visaMobilePhoneNumber: '44123456789'), $this->createPayment());

        $this->assertInstanceOf(PayByVisaMobile::class, $command);
    }

    public function test_it_throws_an_exception_when_trying_to_create_a_command_with_unsupported_factory(): void
    {
        $this->expectException(UnsupportedNextCommandFactory::class);

        $this->createTestSubject()->create($this->createCommand(), new Payment());
    }

    private function createCommand(?string $token = null, ?string $visaMobilePhoneNumber = null): Pay
    {
        return new Pay(
            $token ?? 'token',
            'https://cw.nonexisting/success',
            'https://cw.nonexisting/failure',
            visaMobilePhoneNumber: $visaMobilePhoneNumber,
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
        return new PayByVisaMobileFactory();
    }

}
