<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreatePayByLinkPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreatePayByLinkPayloadFactoryTest extends TestCase
{
    use ProphecyTrait;

    private CreateRedirectBasedPaymentPayloadFactoryInterface|ObjectProphecy $createRedirectBasedPaymentPayloadFactory;

    protected function setUp(): void
    {
        $this->createRedirectBasedPaymentPayloadFactory = $this->prophesize(CreateRedirectBasedPaymentPayloadFactoryInterface::class);
    }

    public function test_it_throws_exception_if_payment_visa_mobile_key_is_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given payment does not have a bank selected.');

        $payment = $this->prophesize(PaymentInterface::class);

        $payment->getDetails()->willReturn(['tpay' => ['some_other_key' => true]]);

        $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, 'https://cw.org/notify', 'pl_PL')->willReturn(['some' => 'data']);

        $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');
    }

    public function test_it_adds_card_related_data_to_a_basic_create_payment_payload_output(): void
    {
        $channelId = '4';
        $payment = $this->prophesize(PaymentInterface::class);

        $payment->getDetails()->willReturn(['tpay' => ['tpay_channel_id' => $channelId]]);
        $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, 'https://cw.org/notify', 'pl_PL')->willReturn(['some' => 'data']);

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');

        $this->assertSame([
            'some' => 'data',
            'pay' => [
                'channelId' => (int) $channelId,
            ],
        ], $payload);
    }

    private function createTestSubject(): CreatePayByLinkPayloadFactory
    {
        return new CreatePayByLinkPayloadFactory($this->createRedirectBasedPaymentPayloadFactory->reveal());
    }
}

