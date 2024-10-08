<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreatePayByLinkPayloadFactoryInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class CreatePayByLinkTransactionAction extends AbstractCreateTransactionAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private readonly CreatePayByLinkPayloadFactoryInterface $createPayByLinkPayloadFactory,
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

        $response = $this->api->transactions()->createTransaction(
            $this->createPayByLinkPayloadFactory->createFrom($model, $notifyToken->getTargetUrl(), $localeCode),
        );

        $paymentDetails = PaymentDetails::fromArray($model->getDetails());

        $paymentDetails->setTransactionId($response['transactionId']);
        $paymentDetails->setStatus($response['status']);
        $paymentDetails->setPaymentUrl($response['transactionPaymentUrl']);

        $model->setDetails($paymentDetails->toArray());

        Assert::notNull($paymentUrl = $paymentDetails->getPaymentUrl());

        throw new HttpRedirect($paymentUrl);
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

        return $paymentDetails->getType() === $paymentDetails::PBL_TYPE;
    }
}
