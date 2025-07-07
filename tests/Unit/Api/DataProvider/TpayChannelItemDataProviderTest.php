<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\DataProvider;

use ApiPlatform\Metadata\Get;
use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\TpayChannelItemDataProvider;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayChannel;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolverInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class TpayChannelItemDataProviderTest extends TestCase
{
    use ProphecyTrait;

    private TpayTransactionChannelResolverInterface|ObjectProphecy $tpayTransactionChannelResolver;

    protected function setUp(): void
    {
        $this->tpayTransactionChannelResolver = $this->prophesize(TpayTransactionChannelResolverInterface::class);
    }


    public function test_it_provides_tpay_channel_item(): void
    {
        $transactionChannels = [
            '1' => ['id' => '1', 'name' => 'Channel 1'],
            '2' => ['id' => '2', 'name' => 'Channel 2'],
        ];

        $this->tpayTransactionChannelResolver->resolve()->willReturn($transactionChannels);

        $operation = new Get();
        $result = $this->createTestSubject()->provide($operation, ['id' => '2'], []);

        $this->assertInstanceOf(TpayChannel::class, $result);
        $this->assertSame('2', $result->getId());
    }

    public function test_it_returns_null_if_tpay_channel_item_does_not_exist(): void
    {
        $transactionChannels = [
            '1' => ['id' => '1', 'name' => 'Channel 1'],
            '2' => ['id' => '2', 'name' => 'Channel 2'],
        ];

        $this->tpayTransactionChannelResolver->resolve()->willReturn($transactionChannels);

        $operation = new Get();
        $result = $this->createTestSubject()->provide($operation, ['id' => '3'], []);

        $this->assertNull($result);
    }

    private function createTestSubject(): TpayChannelItemDataProvider
    {
        return new TpayChannelItemDataProvider(
            $this->tpayTransactionChannelResolver->reveal()
        );
    }

}
