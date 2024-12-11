<?php

declare(strict_types=1);


namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Provider;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\OrderAwareValidTpayChannelListProvider;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\ValidTpayChannelListProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\OrderInterface;

final class OrderAwareValidTpayChannelListProviderTest extends TestCase
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
            'name' => 'BLIK',
            'available' => true,
            'groups' => [
                ['id' => '150'],
            ],
        ],
        '3' => [
            'id' => '3',
            'name' => 'PayPo',
            'available' => true,
            'groups' => [
                ['id' => '169'],
            ],
            'constraints' => [
                [
                    'field' => 'amount',
                    'type' => 'min',
                    'value' => '30.00',
                ],
            ],
        ],
        '4' => [
            'id' => '4',
            'name' => 'PayPo',
            'available' => true,
            'groups' => [
                ['id' => '172'],
            ],
            'constraints' => [
                [
                    'field' => 'amount',
                    'type' => 'max',
                    'value' => '4000.00',
                ],
            ],
        ],
        '5' => [
            'id' => '5',
            'name' => 'BLIK Płacę Później',
            'available' => true,
            'groups' => [
                ['id' => '173'],
            ],
            'constraints' => [
                [
                    'field' => 'amount',
                    'type' => 'min',
                    'value' => '30.00',
                ],
                [
                    'field' => 'amount',
                    'type' => 'max',
                    'value' => '4000.00',
                ],
            ],
        ],
    ];

    /**
     * @dataProvider orderTotalDataProvider
     */
    public function test_it_provides_valid_tpay_channels_for_order(int $orderTotal, array $expectedChannels): void
    {
        $validTpayChannelListProvider = $this->prophesize(ValidTpayChannelListProviderInterface::class);
        $order = $this->prophesize(OrderInterface::class);
        $validTpayChannelListProvider->provide()->willReturn(self::BANK_LIST);
        $order->getTotal()->willReturn($orderTotal);

        $result = (new OrderAwareValidTpayChannelListProvider(
            $validTpayChannelListProvider->reveal(),
        ))->provide($order->reveal());

        $this->assertSame($expectedChannels, array_keys($result));
    }

    public function test_it_does_not_validate_tpay_channels_if_constraints_exist_but_with_missing_fields(): void
    {
        $validTpayChannelListProvider = $this->prophesize(ValidTpayChannelListProviderInterface::class);
        $order = $this->prophesize(OrderInterface::class);
        $validTpayChannelListProvider->provide()->willReturn([
            '1' => [
                'id' => '1',
                'name' => 'I am invalid because of missing field in constraint',
                'available' => true,
                'groups' => [
                    ['id' => '173'],
                ],
                'constraints' => [
                    [
                        'type' => 'min',
                        'value' => '30.00',
                    ]
                ],
            ],
            '2' => [
                'id' => '2',
                'name' => 'I am invalid because of missing type in constraint',
                'available' => true,
                'groups' => [
                    ['id' => '173'],
                ],
                'constraints' => [
                    [
                        'field' => 'amount',
                        'value' => '30.00',
                    ]
                ],
            ],
            '3' => [
                'id' => '3',
                'name' => 'I am invalid because of missing value in constraint',
                'available' => true,
                'groups' => [
                    ['id' => '173'],
                ],
                'constraints' => [
                    [
                        'field' => 'amount',
                        'type' => 'min',
                    ]
                ],
            ],
        ]);
        $order->getTotal()->willReturn(2000);

        $result = (new OrderAwareValidTpayChannelListProvider(
            $validTpayChannelListProvider->reveal(),
        ))->provide($order->reveal());

        $this->assertSame([1, 2, 3], array_keys($result));
    }

    private function orderTotalDataProvider(): array
    {
        return [
            [
                'orderTotal' => 3000_00,
                'expectedChannels' => [1, 2, 3, 4, 5],
            ],
            [
                'orderTotal' => 10_00,
                'expectedChannels' => [1, 2, 4],
            ],
            [
                'orderTotal' => 5000_00,
                'expectedChannels' => [1, 2, 3],
            ],
        ];
    }
}
