<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Generic;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

abstract class BasePaymentAwareAction extends BaseApiAwareAction implements GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;
    use GatewayAwareTrait;

    /**
     * @param Generic $request
     *
     * @throws \Throwable
     */
    public function execute($request): void
    {
        /** @var PaymentInterface $model */
        $model = $request->getModel();
        $paymentDetails = PaymentDetails::fromArray($model->getDetails());
        $gatewayName = $request->getToken()?->getGatewayName() ?? $this->getGatewayNameFrom($model);
        $localeCode = $this->getLocaleCodeFrom($model);

        $this->preExecute($model, $paymentDetails, $gatewayName, $localeCode);

        try {
            $this->doExecute($request, $model, $paymentDetails, $gatewayName, $localeCode);
        } catch (\Throwable $e) {
            $model->setDetails($paymentDetails->toArray());

            throw $e;
        }

        $model->setDetails($paymentDetails->toArray());

        $this->postExecute($model, $paymentDetails, $gatewayName, $localeCode);
    }

    abstract protected function doExecute(Generic $request, PaymentInterface $model, PaymentDetails $paymentDetails, string $gatewayName, string $localeCode): void;

    protected function preExecute(PaymentInterface $model, PaymentDetails $paymentDetails, string $gatewayName, string $localeCode): void
    {
    }

    protected function postExecute(PaymentInterface $model, PaymentDetails $paymentDetails, string $gatewayName, string $localeCode): void
    {
    }

    protected function getLocaleCodeFrom(PaymentInterface $payment): string
    {
        return $payment->getOrder()?->getLocaleCode() ?? throw new \InvalidArgumentException('Cannot determine locale code for a given payment');
    }

    protected function getGatewayNameFrom(PaymentInterface $payment): string
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $payment->getMethod();

        return $paymentMethod?->getGatewayConfig()?->getFactoryName() ?? throw new \InvalidArgumentException('Cannot determine gateway name for a given payment');
    }
}
