<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Provider;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProvider;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolverInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class TpayApiBankListProviderTest extends TestCase
{
    use ProphecyTrait;

    private TpayTransactionChannelResolverInterface|ObjectProphecy $transactionChannelResolver;

    protected function setUp(): void
    {
        $this->transactionChannelResolver = $this->prophesize(TpayTransactionChannelResolverInterface::class);
    }

    public function test_it_does_not_provide_invalid_transaction_channels(): void
    {
        $correctChannel = [
            'id' => '1',
            'instantRedirection' => true,
            'onlinePayment' => true,
            'available' => true,
        ];

        $incorrectChannel = [
            'id' => '2',
            'instantRedirection' => false,
            'onlinePayment' => true,
            'available' => true,
        ];

        $channels = [
            '1' => $correctChannel,
            '2' => $incorrectChannel,
        ];

        $this->transactionChannelResolver->resolve()->willReturn($channels);

        $this->assertSame(
            ['1' => $correctChannel],
            $this->createTestSubject()->provide()
        );
    }

    public function test_it_provides_transaction_channels(): void
    {
        $channels = [
            '1' => [
                'id' => '1',
                'instantRedirection' => true,
                'onlinePayment' => true,
                'available' => true,
            ],
            '2' => [
                'id' => '2',
                'instantRedirection' => true,
                'onlinePayment' => true,
                'available' => true,
            ],
        ];

        $this->transactionChannelResolver->resolve()->willReturn($channels);

        $this->assertSame(
            $channels,
            $this->createTestSubject()->provide()
        );
    }


    private function createTestSubject(): TpayApiBankListProvider
    {
        return new TpayApiBankListProvider($this->transactionChannelResolver->reveal());
    }
}
