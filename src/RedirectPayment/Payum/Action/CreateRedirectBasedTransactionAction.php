<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\RedirectPayment\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\BasePaymentAwareAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\RedirectPayment\Payum\Factory\GatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Generic;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class CreateRedirectBasedTransactionAction extends BasePaymentAwareAction
{
    use GenericTokenFactoryAwareTrait;

    public function __construct(
        private readonly CreateRedirectBasedPaymentPayloadFactoryInterface $createRedirectBasedPaymentPayloadFactory,
        private readonly NotifyTokenFactoryInterface $notifyTokenFactory,
    ) {
        parent::__construct();
    }

    protected function doExecute(Generic $request, PaymentInterface $model, PaymentDetails $paymentDetails, string $gatewayName, string $localeCode): void
    {
        $notifyToken = $this->notifyTokenFactory->create($model, $gatewayName, $localeCode);

        $this->do(
            fn () => $this->api->transactions()->createTransaction(
                $this->createRedirectBasedPaymentPayloadFactory->createFrom($model, $notifyToken->getTargetUrl(), $localeCode),
            ),
            onSuccess: function (array $response) use ($paymentDetails) {
                $paymentDetails->setStatus($response['status']);
                $paymentDetails->setTransactionId($response['transactionId']);
                $paymentDetails->setPaymentUrl($response['transactionPaymentUrl']);
            },
            onFailure: fn () => $paymentDetails->setStatus(PaymentInterface::STATE_FAILED),
        );
    }

    protected function postExecute(PaymentInterface $model, PaymentDetails $paymentDetails, string $gatewayName, string $localeCode): void
    {
        if ($paymentDetails->getPaymentUrl() !== null) {
            throw new HttpRedirect($paymentDetails->getPaymentUrl());
        }
    }

    public function supports($request): bool
    {
        if (!$request instanceof CreateTransaction) {
            return false;
        }

        $model = $request->getModel();

        if (!$model instanceof PaymentInterface) {
            return false;
        }

        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $model->getMethod();
        $gatewayName = $paymentMethod?->getGatewayConfig()?->getFactoryName();

        return $gatewayName === GatewayFactory::NAME;
    }
}
