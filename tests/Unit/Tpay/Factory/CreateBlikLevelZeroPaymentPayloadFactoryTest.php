<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateBlikLevelZeroPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PayGroup;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateBlikLevelZeroPaymentPayloadFactoryTest extends TestCase
{
    use ProphecyTrait;

    private CreateRedirectBasedPaymentPayloadFactoryInterface|ObjectProphecy $createRedirectBasedPaymentPayloadFactory;

    protected function setUp(): void
    {
        $this->createRedirectBasedPaymentPayloadFactory = $this->prophesize(CreateRedirectBasedPaymentPayloadFactoryInterface::class);
    }

    public function test_it_throws_exception_if_blik_token_key_is_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given payment does not have a blik code.');

        $payment = $this->prophesize(PaymentInterface::class);

        $payment->getDetails()->willReturn(['tpay' => ['some_other_key' => true]]);

        $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, 'https://cw.org/notify', 'pl_PL')->willReturn(['some' => 'data']);

        $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');
    }

    public function test_it_adds_card_related_data_to_a_basic_create_payment_payload_output(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);

        $payment->getDetails()->willReturn(['tpay' => ['blik_token' => '777123']]);

        $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, 'https://cw.org/notify', 'pl_PL')->willReturn(['some' => 'data']);

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');

        $this->assertSame([
            'some' => 'data',
            'pay' => [
                'groupId' => PayGroup::BLIK,
                'blikPaymentData' => [
                    'blikToken' => '777123',
                ]
            ],
        ], $payload);
    }

    private function createTestSubject(): CreateBlikLevelZeroPaymentPayloadFactory
    {
        return new CreateBlikLevelZeroPaymentPayloadFactory($this->createRedirectBasedPaymentPayloadFactory->reveal());
    }
}
