<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\BasePaymentAwareAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreatePayByLinkPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PaymentType;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Generic;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreatePayByLinkTransactionAction extends BasePaymentAwareAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private readonly CreatePayByLinkPayloadFactoryInterface $createPayByLinkPayloadFactory,
        private readonly NotifyTokenFactoryInterface $notifyTokenFactory,
    ) {
        parent::__construct();
    }

    protected function doExecute(Generic $request, PaymentInterface $model, PaymentDetails $paymentDetails, string $gatewayName, string $localeCode): void
    {
        $notifyToken = $this->notifyTokenFactory->create($model, $gatewayName, $localeCode);

        $this->do(
            fn () => $this->api->transactions()->createTransaction(
                $this->createPayByLinkPayloadFactory->createFrom($model, $notifyToken->getTargetUrl(), $localeCode),
            ),
            onSuccess: function (array $response) use ($paymentDetails) {
                $paymentDetails->setTransactionId($response['transactionId']);
                $paymentDetails->setStatus($response['status']);
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

        $paymentDetails = PaymentDetails::fromArray($model->getDetails());

        return $paymentDetails->getType() === PaymentType::PAY_BY_LINK;
    }
}
