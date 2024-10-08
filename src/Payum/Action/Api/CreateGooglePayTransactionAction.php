<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateGooglePayPaymentPayloadFactoryInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateGooglePayTransactionAction extends AbstractCreateTransactionAction
{
    use GenericTokenFactoryAwareTrait;

    public function __construct(
        private readonly CreateGooglePayPaymentPayloadFactoryInterface $createGooglePayPaymentPayloadFactory,
        private readonly NotifyTokenFactoryInterface $notifyTokenFactory,
    ) {
        parent::__construct();
    }

    /**
     * @param CreateTransaction $request
     */
    public function execute($request): void
    {
        /** @var PaymentInterface $model */
        $model = $request->getModel();
        $gatewayName = $request->getToken()?->getGatewayName() ?? $this->getGatewayNameFrom($model);
        $localeCode = $this->getLocaleCodeFrom($model);
        $notifyToken = $this->notifyTokenFactory->create($model, $gatewayName, $localeCode);
        $paymentDetails = PaymentDetails::fromArray($model->getDetails());

        $response = $this->api->transactions()->createTransaction(
            $this->createGooglePayPaymentPayloadFactory->createFrom($model, $notifyToken->getTargetUrl(), $localeCode),
        );

        $paymentDetails->setTransactionId($response['transactionId']);
        $paymentDetails->setStatus($response['status']);

        if ($this->is3dSecureRedirectRequired($paymentDetails)) {
            $paymentDetails->setPaymentUrl(
                $response['transactionPaymentUrl'] ?? throw new \InvalidArgumentException('Cannot perform 3DS redirect. Missing transactionPaymentUrl in the response.'),
            );
        }

        $model->setDetails($paymentDetails->toArray());

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

        $details = $model->getDetails();

        return isset($details['tpay']['google_pay_token']);
    }

    private function is3dSecureRedirectRequired(PaymentDetails $paymentDetails): bool
    {
        return $paymentDetails->getStatus() === 'pending';
    }
}
