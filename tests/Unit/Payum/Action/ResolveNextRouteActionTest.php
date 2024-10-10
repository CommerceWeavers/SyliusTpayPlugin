<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\ResolveNextRouteAction;
use CommerceWeavers\SyliusTpayPlugin\Routing;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRoute;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Routing\RouterInterface;

final class ResolveNextRouteActionTest extends TestCase
{
    use ProphecyTrait;

    private GenericTokenFactoryInterface|ObjectProphecy $genericTokenFactory;

    private ResolveNextRoute|ObjectProphecy $request;

    private PaymentInterface|ObjectProphecy $model;

    private RouterInterface|ObjectProphecy $router;

    protected function setUp(): void
    {
        $this->genericTokenFactory = $this->prophesize(GenericTokenFactoryInterface::class);
        $this->request = $this->prophesize(ResolveNextRoute::class);
        $this->model = $this->prophesize(PaymentInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);

        $this->request->getModel()->willReturn($this->model);
    }

    public function test_it_supports_only_resolve_next_route_requests(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new Sync($this->model->reveal())));
        $this->assertTrue($action->supports(new ResolveNextRoute($this->model->reveal())));
    }

    public function test_it_supports_only_payment_interface_based_models(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new ResolveNextRoute(new \stdClass())));
        $this->assertTrue($action->supports(new ResolveNextRoute($this->model->reveal())));
    }

    public function test_it_sets_the_next_route_to_thank_you_page_once_payment_is_completed(): void
    {
        $this->model->getState()->willReturn(PaymentInterface::STATE_COMPLETED);

        $action = $this->createTestSubject();
        $action->execute($this->request->reveal());

        $this->request->setRouteName('sylius_shop_order_thank_you')->shouldHaveBeenCalled();
    }

    public function test_it_sets_the_next_route_to_waiting_for_payment_page_once_payment_is_processing(): void
    {
        $this->model->getState()->willReturn(PaymentInterface::STATE_PROCESSING);

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('tpay');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $order = $this->prophesize(OrderInterface::class);

        $this->model->getMethod()->willReturn($paymentMethod);
        $this->model->getOrder()->willReturn($order);

        $token = $this->prophesize(TokenInterface::class);
        $token->getHash()->willReturn('token_hash');

        $this->genericTokenFactory
            ->createToken('tpay', $this->model->reveal(), Routing::SHOP_WAITING_FOR_PAYMENT)
            ->willReturn($token)
        ;

        $action = $this->createTestSubject();
        $action->execute($this->request->reveal());

        $this->request->setRouteName('commerce_weavers_sylius_tpay_waiting_for_payment')->shouldHaveBeenCalled();
        $this->request->setRouteParameters(['payum_token' => 'token_hash'])->shouldHaveBeenCalled();
    }

    public function test_it_sets_the_next_route_to_order_show_page_once_payment_is_not_completed(): void
    {
        $this->model->getState()->willReturn(PaymentInterface::STATE_NEW);

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('tpay');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $order = $this->prophesize(OrderInterface::class);
        $order->getTokenValue()->willReturn('order_token');

        $this->model->getOrder()->willReturn($order);

        $action = $this->createTestSubject();
        $action->execute($this->request->reveal());

        $this->request->setRouteName('sylius_shop_order_show')->shouldHaveBeenCalled();
        $this->request->setRouteParameters(['tokenValue' => 'order_token'])->shouldHaveBeenCalled();
    }

    private function createTestSubject(): ResolveNextRouteAction
    {
        $action = new ResolveNextRouteAction();

        $action->setGenericTokenFactory($this->genericTokenFactory->reveal());

        return $action;
    }
}
