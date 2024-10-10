<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\PayWithCard;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateCardPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PaymentType;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateCardTransactionAction extends AbstractCreateTransactionAction implements GatewayAwareInterface
{
    use GenericTokenFactoryAwareTrait;
    use GatewayAwareTrait;

    public function __construct(
        private readonly CreateCardPaymentPayloadFactoryInterface $createCardPaymentPayloadFactory,
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
            $this->createCardPaymentPayloadFactory->createFrom($model, $notifyToken->getTargetUrl(), $localeCode),
        );

        $paymentDetails->setTransactionId($response['transactionId']);
        $paymentDetails->setPaymentUrl($response['transactionPaymentUrl']);

        $model->setDetails($paymentDetails->toArray());

        $this->gateway->execute(new PayWithCard($request->getToken() ?? $model));
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

        return $paymentDetails->getType() === PaymentType::CARD;
    }
}
