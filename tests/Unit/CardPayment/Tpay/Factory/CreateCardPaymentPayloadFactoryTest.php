<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\CardPayment\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\CardPayment\Tpay\Factory\CreateCardPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Tpay\Factory\CreateCardPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PayGroup;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateCardPaymentPayloadFactoryTest extends TestCase
{
    use ProphecyTrait;

    private CreateRedirectBasedPaymentPayloadFactoryInterface|ObjectProphecy $createRedirectBasedPaymentPayloadFactory;

    protected function setUp(): void
    {
        $this->createRedirectBasedPaymentPayloadFactory = $this->prophesize(CreateRedirectBasedPaymentPayloadFactoryInterface::class);
    }

    public function test_it_adds_card_related_data_to_a_basic_create_payment_payload_output(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['some' => 'data'])
            ->shouldBeCalled()
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');

        self::assertSame([
            'some' => 'data',
            'pay' => [
                'groupId' => PayGroup::CARD,
            ],
        ], $payload);
    }

    private function createTestSubject(): CreateCardPaymentPayloadFactoryInterface
    {
        return new CreateCardPaymentPayloadFactory($this->createRedirectBasedPaymentPayloadFactory->reveal());
    }
}
