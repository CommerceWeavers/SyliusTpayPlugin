<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Mapper\PayWithCardActionPayloadMapperInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\PayWithCard;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Generic;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

class PayWithCardAction extends BasePaymentAwareAction
{
    public function __construct(private readonly PayWithCardActionPayloadMapperInterface $payWithCardActionPayloadMapper)
    {
        parent::__construct();
    }

    protected function doExecute(Generic $request, PaymentInterface $model, PaymentDetails $paymentDetails, string $gatewayName, string $localeCode): void
    {
        Assert::notNull($paymentDetails->getEncodedCardData(), 'Card data is required to pay with card.');
        Assert::notNull($paymentDetails->getTransactionId(), 'Transaction ID is required to pay with card.');

        $this->do(
            fn () => $this->api->transactions()->createPaymentByTransactionId(
                $this->payWithCardActionPayloadMapper->getPayload($paymentDetails),
                $paymentDetails->getTransactionId(),
            ),
            onSuccess: function ($response) use ($paymentDetails) {
                $paymentDetails->setResult($response['result']);
                $paymentDetails->setStatus($response['status']);
                $paymentDetails->setPaymentUrl($response['transactionPaymentUrl'] ?? null);
            },
            onFailure: fn () => $paymentDetails->setStatus(PaymentInterface::STATE_FAILED),
        );

        $paymentDetails->clearSensitiveData();
    }

    protected function postExecute(PaymentInterface $model, PaymentDetails $paymentDetails, string $gatewayName, string $localeCode): void
    {
        if ($paymentDetails->getPaymentUrl() !== null && $paymentDetails->getPaymentUrl() !== '') {
            throw new HttpRedirect($paymentDetails->getPaymentUrl());
        }
    }

    public function supports($request): bool
    {
        return $request instanceof PayWithCard && $request->getModel() instanceof PaymentInterface;
    }
}
