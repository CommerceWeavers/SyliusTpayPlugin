<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateBlikLevelZeroPaymentPayloadFactoryInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface;
use Tpay\OpenApi\Api\TpayApi;

/**
 * @property TpayApi $api
 */
final class CreateBlikLevelZeroTransactionAction extends AbstractCreateTransactionAction
{
    use GenericTokenFactoryAwareTrait;

    public function __construct(
        private CreateBlikLevelZeroPaymentPayloadFactoryInterface $createBlikLevelZeroPaymentPayloadFactory,
        private NotifyTokenFactoryInterface $notifyTokenFactory,
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
            $this->createBlikLevelZeroPaymentPayloadFactory->createFrom($model, $notifyToken->getTargetUrl(), $localeCode),
        );

        $paymentDetails->setTransactionId($response['transactionId']);
        $paymentDetails->setStatus($response['status']);

        $model->setDetails($paymentDetails->toArray());
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

        return $paymentDetails->getType() === $paymentDetails::BLIK_TYPE;
    }
}
