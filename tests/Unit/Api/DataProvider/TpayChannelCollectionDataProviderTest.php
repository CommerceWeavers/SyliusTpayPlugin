<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\DataProvider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\TpayChannelCollectionDataProvider;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayChannel;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolverInterface;
use Prophecy\Prophecy\ObjectProphecy;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class TpayChannelCollectionDataProviderTest extends TestCase
{
    use ProphecyTrait;

    private TpayTransactionChannelResolverInterface|ObjectProphecy $tpayTransactionChannelResolver;

    protected function setUp(): void
    {
        $this->tpayTransactionChannelResolver = $this->prophesize(TpayTransactionChannelResolverInterface::class);
    }

    public function test_it_provides_tpay_channel_collection(): void
    {
        $transactionChannels = [
            '1' => ['id' => '1', 'name' => 'Channel 1'],
            '2' => ['id' => '2', 'name' => 'Channel 2'],
        ];

        $this->tpayTransactionChannelResolver->resolve()->willReturn($transactionChannels);

        $operation = new GetCollection();
        $result = $this->createTestSubject()->provide($operation, [], []);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(TpayChannel::class, $result[0]);
        $this->assertInstanceOf(TpayChannel::class, $result[1]);
    }


    private function createTestSubject(): TpayChannelCollectionDataProvider
    {
        return new TpayChannelCollectionDataProvider(
            $this->tpayTransactionChannelResolver->reveal()
        );
    }
}
