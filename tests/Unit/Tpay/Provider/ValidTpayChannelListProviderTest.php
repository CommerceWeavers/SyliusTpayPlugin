<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Provider;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Exception\UnableToGetBankListException;
use CommerceWeavers\SyliusTpayPlugin\Repository\PaymentMethodRepositoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\GatewayName;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\AvailableTpayChannelListProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\ValidTpayChannelListProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class ValidTpayChannelListProviderTest extends TestCase
{
    use ProphecyTrait;

    private const BANK_LIST = [
        '1' => [
            'id' => '1',
            'name' => 'some bank',
            'available' => true,
            'groups' => [
                ['id' => '1'],
            ],
        ],
        '2' => [
            'id' => '2',
            'name' => 'card payment',
            'available' => true,
            'groups' => [
                ['id' => '103'],
            ],
        ],
        '3' => [
            'id' => '3',
            'name' => 'BLIK',
            'available' => true,
            'groups' => [
                ['id' => '150'],
            ],
        ],
        '4' => [
            'id' => '4',
            'name' => 'Apple Pay',
            'available' => true,
            'groups' => [
                ['id' => '170'],
            ],
        ],
        '5' => [
            'id' => '5',
            'name' => 'Google Pay',
            'available' => true,
            'groups' => [
                ['id' => '166'],
            ],
        ],
        '6' => [
            'id' => '6',
            'name' => 'Visa mobile',
            'available' => true,
            'groups' => [
                ['id' => '171'],
            ],
        ],
        '7' => [
            'id' => '7',
            'name' => 'Visa mobile on site',
            'available' => true,
            'groups' => [
                ['id' => '177'],
            ],
        ],
    ];

    private AvailableTpayChannelListProviderInterface|ObjectProphecy $availableTpayApiBankListProvider;

    private PaymentMethodRepositoryInterface|ObjectProphecy $paymentMethodRepository;

    private ChannelContextInterface|ObjectProphecy $channelContext;

    protected function setUp(): void
    {
        $this->availableTpayApiBankListProvider = $this->prophesize(AvailableTpayChannelListProviderInterface::class);
        $this->paymentMethodRepository = $this->prophesize(PaymentMethodRepositoryInterface::class);
        $this->channelContext = $this->prophesize(ChannelContextInterface::class);
    }

    public function test_it_throws_exception_if_there_is_no_tpay_based_payment(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no payment method of Tpay type available');

        $this->availableTpayApiBankListProvider->provide()->willReturn(self::BANK_LIST);

        $channel = $this->prophesize(ChannelInterface::class);
        $this->channelContext->getChannel()->willReturn($channel->reveal());

        $this->paymentMethodRepository->findByChannelAndGatewayConfigNameWithGatewayConfig(
            $channel,
            GatewayName::all(),
        )->willReturn([]);

        $this->createTestSubject()->provide();
    }

    public function test_it_throws_exception_if_there_is_no_gateway_config_that_is_pbl_type(): void
    {
        $this->expectException(UnableToGetBankListException::class);
        $this->expectExceptionMessage('Bank list cannot be retrieved if there is no payment method with PayByLink type configured');

        $notTpayBasedPaymentMethod = $this->prophesize(PaymentMethodInterface::class);

        $channel = $this->prophesize(ChannelInterface::class);
        $this->channelContext->getChannel()->willReturn($channel->reveal());

        $this->paymentMethodRepository
            ->findByChannelAndGatewayConfigNameWithGatewayConfig($channel, GatewayName::all())
            ->willReturn([
                $notTpayBasedPaymentMethod
            ])
        ;
        $this->paymentMethodRepository
            ->findByChannelAndGatewayConfigNameWithGatewayConfig($channel, [GatewayName::PAY_BY_LINK])
            ->willReturn([])
        ;

        $this->createTestSubject()->provide();
    }

    public function test_it_returns_all_available_payments_if_only_tpay_payment_method_is_pbl(): void
    {
        $tpayBasedPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $tpayPblGatewayConfig = $this->prophesize(GatewayConfig::class);
        $tpayPblGatewayConfig->getFactoryName()->willReturn(GatewayName::PAY_BY_LINK);
        $tpayBasedPaymentMethod->getGatewayConfig()->willReturn($tpayPblGatewayConfig);

        $this->availableTpayApiBankListProvider->provide()->willReturn(self::BANK_LIST);

        $channel = $this->prophesize(ChannelInterface::class);
        $this->channelContext->getChannel()->willReturn($channel->reveal());

        $this->paymentMethodRepository
            ->findByChannelAndGatewayConfigNameWithGatewayConfig($channel, GatewayName::all())
            ->willReturn([
                $tpayBasedPaymentMethod->reveal(),
            ])
        ;
        $this->paymentMethodRepository
            ->findByChannelAndGatewayConfigNameWithGatewayConfig($channel, [GatewayName::PAY_BY_LINK])
            ->willReturn([
                $this->prophesize(PaymentMethodInterface::class)->reveal(),
            ])
        ;

        $result = $this->createTestSubject()->provide();

        $this->assertSame(self::BANK_LIST, $result);
    }

    public function test_it_returns_valid_payments_according_to_available_tpay_payment_methods(): void
    {
        $tpayPblPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $tpayPblPaymentMethod->getGatewayConfig()->willReturn($tpayPblGatewayConfig = $this->prophesize(GatewayConfig::class));

        $tpayPblGatewayConfig->getFactoryName()->willReturn(GatewayName::PAY_BY_LINK);

        $anotherTpayPblPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $anotherTpayPblPaymentMethod->getGatewayConfig()->willReturn($anotherTpayPblGatewayConfig = $this->prophesize(GatewayConfig::class));

        $anotherTpayPblGatewayConfig->getFactoryName()->willReturn(GatewayName::VISA_MOBILE);

        $this->availableTpayApiBankListProvider->provide()->willReturn(self::BANK_LIST);

        $channel = $this->prophesize(ChannelInterface::class);
        $this->channelContext->getChannel()->willReturn($channel->reveal());

        $this->paymentMethodRepository
            ->findByChannelAndGatewayConfigNameWithGatewayConfig($channel, GatewayName::all())
            ->willReturn([
                $tpayPblPaymentMethod->reveal(),
                $anotherTpayPblPaymentMethod->reveal(),
            ])
        ;
        $this->paymentMethodRepository
            ->findByChannelAndGatewayConfigNameWithGatewayConfig($channel, [GatewayName::PAY_BY_LINK])
            ->willReturn([
                $this->prophesize(PaymentMethodInterface::class)->reveal(),
            ])
        ;

        $result = $this->createTestSubject()->provide();

        $expected = self::BANK_LIST;
        // unsets both methods for visa mobile and its onsite version
        unset($expected['6'], $expected['7']);

        $this->assertSame($expected, $result);
    }

    public function test_it_returns_valid_payments_even_if_gateway_config_lacks_type(): void
    {
        $tpayPblPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $tpayPblPaymentMethod->getGatewayConfig()->willReturn($tpayPblGatewayConfig = $this->prophesize(GatewayConfig::class));

        $tpayPblGatewayConfig->getFactoryName()->willReturn(GatewayName::PAY_BY_LINK);

        $anotherTpayPblPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $anotherTpayPblPaymentMethod->getGatewayConfig()->willReturn($anotherTpayPblGatewayConfig = $this->prophesize(GatewayConfig::class));

        $anotherTpayPblGatewayConfig->getFactoryName()->willReturn('i_should_still_work');

        $this->availableTpayApiBankListProvider->provide()->willReturn(self::BANK_LIST);

        $channel = $this->prophesize(ChannelInterface::class);
        $this->channelContext->getChannel()->willReturn($channel->reveal());

        $this->paymentMethodRepository
            ->findByChannelAndGatewayConfigNameWithGatewayConfig($channel, GatewayName::all())
            ->willReturn([
                $tpayPblPaymentMethod->reveal(),
                $anotherTpayPblPaymentMethod->reveal(),
            ])
        ;
        $this->paymentMethodRepository
            ->findByChannelAndGatewayConfigNameWithGatewayConfig($channel, [GatewayName::PAY_BY_LINK])
            ->willReturn([
                $this->prophesize(PaymentMethodInterface::class)->reveal(),
            ])
        ;

        $result = $this->createTestSubject()->provide();

        $this->assertSame(self::BANK_LIST, $result);
    }

    private function createTestSubject(): ValidTpayChannelListProvider
    {
        return new ValidTpayChannelListProvider(
            $this->availableTpayApiBankListProvider->reveal(),
            $this->paymentMethodRepository->reveal(),
            $this->channelContext->reveal(),
        );
    }
}
