<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Resolver;

use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\GetTpayTransactionsChannels;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolver;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class TpayTransactionChannelResolverTest extends TestCase
{
    use ProphecyTrait;

    private Payum|ObjectProphecy $payum;

    protected function setUp(): void
    {
        $this->payum = $this->prophesize(Payum::class);
    }

    public function test_it_resolved_indexed_tpay_transaction_channels(): void
    {
        $this->markTestSkipped('Issue with GetTpayTransactionsChannels');

        $gateway = $this->prophesize(GatewayInterface::class);
        $tpayTransactionChannels = $this->prophesize(GetTpayTransactionsChannels::class);

        $result = [
            'result' => 'success',
            'channels' => [
                [
                    'id' => '1',
                    'data' => 'some data',
                ],
                [
                    'id' => '2',
                    'data' => 'some data',
                ],
            ],
        ];

        $indexedResult = [
            '1' => [
                'id' => '1',
                'data' => 'some data',
            ],
            '2' => [
                'id' => '2',
                'data' => 'some data',
            ],
        ];

        $this->payum->getGateway('tpay')->willReturn($gateway);
        $tpayTransactionChannels->setResult($result);
        $tpayTransactionChannels->getResult()->willReturn($result);

        $gateway->execute($tpayTransactionChannels->reveal(), true)->shouldBeCalled();

        $this->assertSame($indexedResult,
            $this->createTestSubject()->resolve()
        );
    }

    private function createTestSubject(): TpayTransactionChannelResolver
    {
        return new TpayTransactionChannelResolver($this->payum->reveal());
    }
}
