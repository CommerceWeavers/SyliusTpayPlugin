<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Controller;

use CommerceWeavers\SyliusTpayPlugin\Command\CancelLastPayment;
use CommerceWeavers\SyliusTpayPlugin\Controller\RetryPaymentAction;
use CommerceWeavers\SyliusTpayPlugin\Payment\Exception\PaymentCannotBeCancelledException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class RetryPaymentActionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|CsrfTokenManagerInterface $csrf;
    private ObjectProphecy|MessageBusInterface $bus;
    private ObjectProphecy|OrderRepositoryInterface $orders;
    private ObjectProphecy|RouterInterface $router;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->csrf = $this->prophesize(CsrfTokenManagerInterface::class);
        $this->bus = $this->prophesize(MessageBusInterface::class);
        $this->orders = $this->prophesize(OrderRepositoryInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->requestStack = new RequestStack();

        $this->csrf->isTokenValid(Argument::any())->willReturn(true);
    }

    private function createRequest(): Request
    {
        $request = new Request([], ['_csrf_token' => 'token']);
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $request->setSession($session);
        $this->requestStack->push($request);

        return $request;
    }

    private function createAction(): RetryPaymentAction
    {
        return new RetryPaymentAction(
            $this->csrf->reveal(),
            $this->bus->reveal(),
            $this->orders->reveal(),
            $this->router->reveal(),
            $this->requestStack
        );
    }

    public function test_it_reuses_existing_new_payment_and_skips_cancellation(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getTokenValue()->willReturn('ORD');
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($this->prophesize(PaymentInterface::class)->reveal());

        $this->orders->findOneByTokenValue('ORD')->willReturn($order->reveal());
        $this->router->generate('sylius_shop_order_show', ['tokenValue' => 'ORD'])->willReturn('/order');
        $this->bus->dispatch(Argument::any())->shouldNotBeCalled();

        $response = $this->createAction()($this->createRequest(), 'ORD');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/order', $response->headers->get('Location'));
    }

    public function test_it_cancels_when_no_new_payment_exists(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getTokenValue()->willReturn('ORD');
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn(null);

        $this->orders->findOneByTokenValue('ORD')->willReturn($order->reveal());
        $this->router->generate('sylius_shop_order_show', ['tokenValue' => 'ORD'])->willReturn('/order');

        $this->bus->dispatch(Argument::that(
            fn($msg) => $msg instanceof CancelLastPayment && $msg->orderToken === 'ORD'
        ))->willReturn(new Envelope(new \stdClass()))->shouldBeCalled();

        $response = $this->createAction()($this->createRequest(), 'ORD');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/order', $response->headers->get('Location'));
    }

    public function test_it_handles_cannot_be_cancelled(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getTokenValue()->willReturn('ORD');
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn(null);

        $this->orders->findOneByTokenValue('ORD')->willReturn($order->reveal());
        $this->router->generate('sylius_shop_homepage')->willReturn('/');

        $this->bus->dispatch(Argument::any())->willThrow(
            new HandlerFailedException(
                new Envelope(new \stdClass()),
                [new PaymentCannotBeCancelledException($this->prophesize(PaymentInterface::class)->reveal())]
            )
        );

        $response = $this->createAction()($this->createRequest(), 'ORD');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/', $response->headers->get('Location'));
    }
}
