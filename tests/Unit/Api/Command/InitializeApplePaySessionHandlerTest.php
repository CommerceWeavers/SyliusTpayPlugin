<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Exception\OrderCannotBeFoundException;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\InitializeApplePaySession;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\InitializeApplePaySessionHandler;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\InitializeApplePayPayment;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

final class InitializeApplePaySessionHandlerTest extends TestCase
{
    use ProphecyTrait;

    private OrderRepositoryInterface|ObjectProphecy $orderRepository;

    private GatewayInterface|ObjectProphecy $gateway;

    protected function setUp(): void
    {
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $this->gateway = $this->prophesize(GatewayInterface::class);
    }

    public function test_it_throws_an_exception_if_an_order_with_the_given_token_does_not_exist(): void
    {
        $this->expectException(OrderCannotBeFoundException::class);
        $this->expectExceptionMessage('Order with token "t0k3n" cannot be found.');

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn(null);

        $this->createTestSubject()($this->createCommand());
    }

    public function test_it_initializes_an_apple_pay_session(): void
    {
        $order = $this->prophesize(OrderInterface::class);

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order);

        $this->gateway->execute(Argument::that(function (InitializeApplePayPayment $request): bool {
            $request->getOutput()->replace([
                'result' => 'result',
                'session' => 'session',
            ]);

            return true;
        }))->shouldBeCalled();

        $result = $this->createTestSubject()($this->createCommand());

        $this->assertSame('result', $result->result);
        $this->assertSame('session', $result->session);
    }

    private function createCommand(): InitializeApplePaySession
    {
        return new InitializeApplePaySession(
            orderToken: 't0k3n',
            domainName: 'cw.nonexisting',
            displayName: 'Commerce Weavers',
            validationUrl: 'https://cw.nonexisting/validation',
        );
    }

    private function createTestSubject(): InitializeApplePaySessionHandler
    {
        return new InitializeApplePaySessionHandler($this->orderRepository->reveal(), $this->gateway->reveal());
    }
}
