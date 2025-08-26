<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateVisaMobilePaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PayGroup;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateVisaMobilePaymentPayloadFactoryTest extends TestCase
{
    use ProphecyTrait;

    private CreateRedirectBasedPaymentPayloadFactoryInterface|ObjectProphecy $createRedirectBasedPaymentPayloadFactory;

    protected function setUp(): void
    {
        $this->createRedirectBasedPaymentPayloadFactory = $this->prophesize(CreateRedirectBasedPaymentPayloadFactoryInterface::class);
    }

    public function test_it_throws_exception_if_payment_visa_mobile_key_is_missing(): void
    {
       self::expectException(\InvalidArgumentException::class);
       self::expectExceptionMessage('The given payment has no visa mobile phone number.');

        $payment = $this->prophesize(PaymentInterface::class);

        $payment->getDetails()->willReturn(['tpay' => ['some_other_key' => true]]);

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['some' => 'data'])
            ->shouldBeCalled()
        ;

        $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');
    }

    public function test_it_adds_card_related_data_to_a_basic_create_payment_payload_output(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);

        $payment->getDetails()->willReturn(['tpay' => ['visa_mobile_phone_number' => '44123456789']]);

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['some' => 'data'])
            ->shouldBeCalled()
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');

        self::assertSame([
            'some' => 'data',
            'payer' => [
                'phone' => '44123456789',
            ],
            'pay' => [
                'groupId' => PayGroup::VISA_MOBILE,
            ],
        ], $payload);
    }

    public function test_it_removes_unsupported_payer_data_from_base_payload(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);

        $payment->getDetails()->willReturn(['tpay' => ['visa_mobile_phone_number' => '123123123']]);

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn([
                'some' => 'data',
                'payer' => [
                    'phone' => '123123123',
                    'city' => 'Warsaw',
                    'ip' => '127.0.0.1',
                    'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                ],
            ])
            ->shouldBeCalled()
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');

        self::assertSame([
            'some' => 'data',
            'payer' => [
                'phone' => '123123123',
                'city' => 'Warsaw',
            ],
            'pay' => [
                'groupId' => PayGroup::VISA_MOBILE,
            ],
        ], $payload);
    }

    private function createTestSubject(): CreateVisaMobilePaymentPayloadFactory
    {
        return new CreateVisaMobilePaymentPayloadFactory($this->createRedirectBasedPaymentPayloadFactory->reveal());
    }
}
