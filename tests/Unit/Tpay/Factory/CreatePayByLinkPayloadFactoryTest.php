<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreatePayByLinkPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreatePayByLinkPayloadFactoryInterface;
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

    public function test_it_returns_redirect_payload_when_no_channel_is_selected(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => []]);

        $basePayload = [
            'amount' => '10.50',
            'description' => 'Test order',
            'payer' => [
                'email' => 'test@example.com',
                'name' => 'Test User',
            ],
            'callbacks' => [
                'payerUrls' => [
                    'success' => 'https://example.com/success',
                    'error' => 'https://example.com/error',
                ],
                'notification' => [
                    'url' => 'https://example.com/notify',
                ],
            ],
        ];

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://example.com/notify', 'pl_PL')
            ->willReturn($basePayload)
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://example.com/notify', 'pl_PL');

        $this->assertSame($basePayload, $payload);
        $this->assertArrayNotHasKey('pay', $payload);
    }

    public function test_it_returns_redirect_payload_when_channel_id_is_null(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['tpay_channel_id' => null]]);

        $basePayload = [
            'amount' => '10.50',
            'description' => 'Test order',
            'payer' => [
                'email' => 'test@example.com',
                'name' => 'Test User',
            ],
            'callbacks' => [
                'payerUrls' => [
                    'success' => 'https://example.com/success',
                    'error' => 'https://example.com/error',
                ],
                'notification' => [
                    'url' => 'https://example.com/notify',
                ],
            ],
        ];

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://example.com/notify', 'pl_PL')
            ->willReturn($basePayload)
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://example.com/notify', 'pl_PL');

        $this->assertSame($basePayload, $payload);
        $this->assertArrayNotHasKey('pay', $payload);
    }

    public function test_it_returns_redirect_payload_when_channel_id_is_empty_string(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['tpay_channel_id' => '']]);

        $basePayload = [
            'amount' => '10.50',
            'description' => 'Test order',
            'payer' => [
                'email' => 'test@example.com',
                'name' => 'Test User',
            ],
            'callbacks' => [
                'payerUrls' => [
                    'success' => 'https://example.com/success',
                    'error' => 'https://example.com/error',
                ],
                'notification' => [
                    'url' => 'https://example.com/notify',
                ],
            ],
        ];

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://example.com/notify', 'pl_PL')
            ->willReturn($basePayload)
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://example.com/notify', 'pl_PL');

        $this->assertSame($basePayload, $payload);
        $this->assertArrayNotHasKey('pay', $payload);
    }

    public function test_it_adds_channel_id_to_payload_when_bank_is_selected(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['tpay_channel_id' => '123']]);

        $basePayload = [
            'amount' => '10.50',
            'description' => 'Test order',
            'payer' => [
                'email' => 'test@example.com',
                'name' => 'Test User',
            ],
            'callbacks' => [
                'payerUrls' => [
                    'success' => 'https://example.com/success',
                    'error' => 'https://example.com/error',
                ],
                'notification' => [
                    'url' => 'https://example.com/notify',
                ],
            ],
        ];

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://example.com/notify', 'pl_PL')
            ->willReturn($basePayload)
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://example.com/notify', 'pl_PL');

        $expectedPayload = $basePayload;
        $expectedPayload['pay'] = ['channelId' => 123];

        $this->assertSame($expectedPayload, $payload);
    }

    public function test_it_converts_string_channel_id_to_integer(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['tpay_channel_id' => '456']]);

        $basePayload = [
            'amount' => '10.50',
            'description' => 'Test order',
        ];

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://example.com/notify', 'pl_PL')
            ->willReturn($basePayload)
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://example.com/notify', 'pl_PL');

        $this->assertSame(['channelId' => 456], $payload['pay']);
        $this->assertIsInt($payload['pay']['channelId']);
    }

    public function test_it_handles_numeric_string_channel_id(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['tpay_channel_id' => '789']]);

        $basePayload = [
            'amount' => '10.50',
            'description' => 'Test order',
        ];

        $this->createRedirectBasedPaymentPayloadFactory
            ->createFrom($payment, 'https://example.com/notify', 'pl_PL')
            ->willReturn($basePayload)
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://example.com/notify', 'pl_PL');

        $this->assertSame(['channelId' => 789], $payload['pay']);
        $this->assertIsInt($payload['pay']['channelId']);
    }

    private function createTestSubject(): CreatePayByLinkPayloadFactoryInterface
    {
        return new CreatePayByLinkPayloadFactory(
            $this->createRedirectBasedPaymentPayloadFactory->reveal(),
        );
    }
}