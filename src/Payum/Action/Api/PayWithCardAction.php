<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\PayWithCard;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PayGroup;
use Payum\Core\Reply\HttpRedirect;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

class PayWithCardAction extends BaseApiAwareAction
{
    /**
     * @param PayWithCard $request
     */
    public function execute($request): void
    {
        /** @var PaymentInterface $model */
        $model = $request->getModel();
        $paymentDetails = PaymentDetails::fromArray($model->getDetails());

        Assert::notNull($paymentDetails->getEncodedCardData(), 'Card data is required to pay with card.');
        Assert::notNull($paymentDetails->getTransactionId(), 'Transaction ID is required to pay with card.');

        $response = $this->api->transactions()->createPaymentByTransactionId([
            'groupId' => PayGroup::CARD,
            'cardPaymentData' => [
                'card' => $paymentDetails->getEncodedCardData(),
            ],
        ], $paymentDetails->getTransactionId());

        $paymentDetails->clearSensitiveData();
        $paymentDetails->setResult($response['result']);
        $paymentDetails->setStatus($response['status']);
        $paymentDetails->setPaymentUrl($response['transactionPaymentUrl']);

        $model->setDetails($paymentDetails->toArray());

        if ($paymentDetails->getPaymentUrl() !== null) {
            throw new HttpRedirect($paymentDetails->getPaymentUrl());
        }
    }

    public function supports($request): bool
    {
        return $request instanceof PayWithCard && $request->getModel() instanceof PaymentInterface;
    }
}
