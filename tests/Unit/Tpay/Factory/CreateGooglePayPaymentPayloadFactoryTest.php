<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateGooglePayPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateGooglePayPaymentPayloadFactoryTest extends TestCase
{
    use ProphecyTrait;

    private CreateRedirectBasedPaymentPayloadFactoryInterface|ObjectProphecy $createRedirectBasedPaymentPayloadFactory;

    protected function setUp(): void
    {
        $this->createRedirectBasedPaymentPayloadFactory = $this->prophesize(CreateRedirectBasedPaymentPayloadFactoryInterface::class);
    }

    public function test_it_adds_google_pay_related_data_to_a_basic_create_payment_payload_output(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['some' => 'data'])
        ;
        $payment->getDetails()->willReturn(['tpay' => ['google_pay_token' => 'YmxhaGJsYWhibGFo']]);

        $payload = $this
            ->createTestSubject()
            ->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL')
        ;

        $this->assertSame([
            'some' => 'data',
            'pay' => [
                'groupId' => 166,
                'googlePayPaymentData' => 'YmxhaGJsYWhibGFo',
            ],
        ], $payload);
    }

    /** @dataProvider invalidPaymentDetailsDataProvider */
    public function test_it_throws_exception_if_payment_details_does_not_contain_google_pay_token(
        array $paymentDetails,
    ): void {
        $payment = $this->prophesize(PaymentInterface::class);
        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['some' => 'data'])
        ;
        $payment->getDetails()->willReturn($paymentDetails);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given payment does not have a Google Pay token.');

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

    private function createTestSubject(): CreateGooglePayPaymentPayloadFactory
    {
        return new CreateGooglePayPaymentPayloadFactory($this->createRedirectBasedPaymentPayloadFactory->reveal());
    }
}
