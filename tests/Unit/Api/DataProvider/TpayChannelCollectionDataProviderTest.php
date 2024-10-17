<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\DataProvider;

use CommerceWeavers\SyliusTpayPlugin\Api\DataProvider\TpayChannelCollectionDataProvider;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayChannel;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolverInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Product\Model\ProductInterface;

final class TpayChannelCollectionDataProviderTest extends TestCase
{
    use ProphecyTrait;

    private TpayTransactionChannelResolverInterface|ObjectProphecy $tpayTransactionChannelResolver;

    protected function setUp(): void
    {
        $this->tpayTransactionChannelResolver = $this->prophesize(TpayTransactionChannelResolverInterface::class);
    }

    public function test_it_does_not_support_if_resource_class_is_not_tpay_channel_class(): void
    {

        $supports = $this->createTestSubject()->supports(
            ProductInterface::class,
            Request::METHOD_GET,
        );

        $this->assertFalse($supports);
    }

    public function test_it_support_if_resource_class_is_tpay_channel_class(): void
    {

        $supports = $this->createTestSubject()->supports(
            TpayChannel::class,
            Request::METHOD_GET,
        );

        $this->assertTrue($supports);
    }

    public function test_it_returns_tpay_channel_collection(): void
    {
        $transactionChannels = [
            '1' => ['id' => '1', 'name' => 'Channel 1'],
            '2' => ['id' => '2', 'name' => 'Channel 2'],
        ];

        $this->tpayTransactionChannelResolver->resolve()->willReturn($transactionChannels);

        $result = iterator_to_array($this->createTestSubject()->getCollection(TpayChannel::class));

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
