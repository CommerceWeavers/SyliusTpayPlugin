<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Form\EventListener;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\SetTpayDefaultPaymentImageUrlListener;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormInterface;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;
use Tpay\OpenApi\Utilities\Cache;

final class SetTpayDefaultPaymentImageUrlListenerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<TpayApi> $tpayApi */
    private ObjectProphecy $tpayApi;

    /** @var ObjectProphecy<Cache> $cache */
    private ObjectProphecy $cache;

    protected function setUp(): void
    {
        $this->tpayApi = $this->prophesize(TpayApi::class);
        $this->cache = $this->prophesize(Cache::class);
    }

    public function test_it_sets_default_image_url_on_pre_submit_event(): void
    {
        $transactionsApi = $this->prophesize(TransactionsApi::class);
        $transactionsApi
            ->getChannels()
            ->shouldBeCalled()
            ->willReturn(['channels' => [
                ['id' => '123', 'image' => ['url' => 'http://example.com/image.jpg']],
                ['id' => '456', 'image' => ['url' => 'http://example.com/456image.jpg']],
            ]])
        ;

        $this->tpayApi
            ->transactions()
            ->shouldBeCalled()
            ->willReturn($transactionsApi->reveal())
        ;

        $event = new PreSubmitEvent(
            $this->prophesize(FormInterface::class)->reveal(),
            ['gatewayConfig' => ['config' => ['tpay_channel_id' => '123']]],
        );

        $this->createTestSubject()->__invoke($event);

        self::assertSame(
            ['gatewayConfig' => ['config' => ['tpay_channel_id' => '123']], 'defaultImageUrl' => 'http://example.com/image.jpg'],
            $event->getData(),
        );
    }

    public function test_it_does_nothing_if_there_is_no_gateway_config(): void
    {
        $this->tpayApi
            ->transactions()
            ->shouldNotBeCalled()
        ;

        $event = new PreSubmitEvent(
            $this->prophesize(FormInterface::class)->reveal(),
            ['gatewayConfig' => []],
        );

        $this->createTestSubject()->__invoke($event);

        self::assertSame(
            ['gatewayConfig' => []],
            $event->getData(),
        );
    }

    public function test_it_does_nothing_if_there_is_no_tpay_channel_id_provided(): void
    {
        $this->tpayApi
            ->transactions()
            ->shouldNotBeCalled()
        ;

        $event = new PreSubmitEvent(
            $this->prophesize(FormInterface::class)->reveal(),
            ['gatewayConfig' => ['config' => ['merchant_id' => '123']]],
        );

        $this->createTestSubject()->__invoke($event);

        self::assertSame(
            ['gatewayConfig' => ['config' => ['merchant_id' => '123']]],
            $event->getData(),
        );
    }

    public function test_it_sets_default_image_url_to_null_if_tpay_image_url_was_not_found(): void
    {
        $transactionsApi = $this->prophesize(TransactionsApi::class);
        $transactionsApi
            ->getChannels()
            ->shouldBeCalled()
            ->willReturn(['channels' => [
                ['id' => '456', 'image' => ['url' => 'http://example.com/456image.jpg']],
            ]])
        ;

        $this->tpayApi
            ->transactions()
            ->shouldBeCalled()
            ->willReturn($transactionsApi->reveal())
        ;

        $event = new PreSubmitEvent(
            $this->prophesize(FormInterface::class)->reveal(),
            ['gatewayConfig' => ['config' => ['tpay_channel_id' => '123']]],
        );

        $this->createTestSubject()->__invoke($event);

        self::assertSame(
            ['gatewayConfig' => ['config' => ['tpay_channel_id' => '123']], 'defaultImageUrl' => null],
            $event->getData(),
        );
    }

    private function createTestSubject(): SetTpayDefaultPaymentImageUrlListener
    {
        $subject = new SetTpayDefaultPaymentImageUrlListener($this->cache->reveal());
        $subject->setTpayApi($this->tpayApi->reveal());

        return $subject;
    }
}
