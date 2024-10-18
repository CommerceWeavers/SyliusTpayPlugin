<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateApplePayPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateApplePayPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateApplePayPaymentPayloadFactoryTest extends TestCase
{
    use ProphecyTrait;

    private CreateRedirectBasedPaymentPayloadFactoryInterface|ObjectProphecy $createRedirectBasedPaymentPayloadFactory;

    protected function setUp(): void
    {
        $this->createRedirectBasedPaymentPayloadFactory = $this->prophesize(CreateRedirectBasedPaymentPayloadFactoryInterface::class);
    }

    public function test_it_adds_apple_pay_related_data_to_a_basic_create_payment_payload_output(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['apple_pay_token' => 'ewogInRwYXkiIDogIkhlbGxvIFdvcmxkIgp9']]);

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['some' => 'data'])
        ;

        $payload = $this
            ->createTestSubject()
            ->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL')
        ;

        $this->assertSame([
            'some' => 'data',
            'pay' => [
                'groupId' => 170,
                'applePayPaymentData' => 'ewogInRwYXkiIDogIkhlbGxvIFdvcmxkIgp9',
            ],
        ], $payload);
    }

    /** @dataProvider invalidPaymentDetailsDataProvider */
    public function test_it_throws_exception_if_payment_details_does_not_contain_google_pay_token(
        array $paymentDetails,
    ): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given payment does not have an Apple Pay token.');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn($paymentDetails);

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['some' => 'data'])
        ;

        $this
            ->createTestSubject()
            ->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL')
        ;
    }

    private function invalidPaymentDetailsDataProvider(): array
    {
        return [
            [['tpay' => ['something' => 'useless']]],
            [['something' => 'useless']],
        ];
    }

    private function createTestSubject(): CreateApplePayPaymentPayloadFactoryInterface
    {
        return new CreateApplePayPaymentPayloadFactory($this->createRedirectBasedPaymentPayloadFactory->reveal());
    }
}
