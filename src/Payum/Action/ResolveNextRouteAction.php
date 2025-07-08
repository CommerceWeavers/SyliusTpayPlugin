<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\Routing;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRoute;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class ResolveNextRouteAction implements ActionInterface, GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    /**
     * @param ResolveNextRoute $request
     */
    public function execute($request): void
    {
        /** @var PaymentInterface $model */
        $model = $request->getModel();

        /** @var OrderInterface $order */
        $order = $model->getOrder();

        if ($model->getState() === PaymentInterface::STATE_COMPLETED) {
            $request->setRouteName(Routing::SHOP_THANK_YOU);
            $request->setRouteParameters([
                'orderToken' => $order->getTokenValue(),
            ]);

            return;
        }

        if ($model->getState() === PaymentInterface::STATE_PROCESSING) {
            $request->setRouteName(Routing::SHOP_WAITING_FOR_PAYMENT);
            $request->setRouteParameters([
                'payum_token' => $this->createTokenForRoute(
                    $model,
                    Routing::SHOP_WAITING_FOR_PAYMENT,
                ),
            ]);

            return;
        }

        if ($model->getState() === PaymentInterface::STATE_FAILED) {
            $request->setRouteName(Routing::SHOP_PAYMENT_FAILED);
            $request->setRouteParameters([
                'orderToken' => $order->getTokenValue(),
            ]);

            return;
        }

        $request->setRouteName('sylius_shop_order_show');
        $request->setRouteParameters(['tokenValue' => $order->getTokenValue()]);
    }

    private function createTokenForRoute(PaymentInterface $payment, string $route): string
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $payment->getMethod();
        $gatewayConfig = $paymentMethod?->getGatewayConfig();

        Assert::notNull($gatewayConfig, 'Payment method must have a gateway config.');

        $gatewayName = $gatewayConfig->getGatewayName();
        Assert::notNull($gatewayName, 'Gateway config must have a gateway name.');

        return $this->tokenFactory->createToken(
            $gatewayName,
            $payment,
            $route,
        )->getHash();
    }

    public function supports($request): bool
    {
        return $request instanceof ResolveNextRoute && $request->getModel() instanceof PaymentInterface;
    }
}
