<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\BlikPayment\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Payum\Exception\BlikAliasAmbiguousValueException;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Repository\BlikAliasRepositoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\BasePaymentAwareAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateBlikLevelZeroPaymentPayloadFactoryInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateBlikLevelZeroTransactionAction extends BasePaymentAwareAction
{
    use GenericTokenFactoryAwareTrait;

    public function __construct(
        private readonly CreateBlikLevelZeroPaymentPayloadFactoryInterface $createBlikLevelZeroPaymentPayloadFactory,
        private readonly NotifyTokenFactoryInterface $notifyTokenFactory,
        private readonly BlikAliasRepositoryInterface $blikAliasRepository,
    ) {
        parent::__construct();
    }

    protected function doExecute(Generic $request, PaymentInterface $model, PaymentDetails $paymentDetails, string $gatewayName, string $localeCode): void
    {
        $notifyToken = $this->notifyTokenFactory->create($model, $gatewayName, $localeCode);

        $blikAlias = null !== $paymentDetails->getBlikAliasValue()
            ? $this->blikAliasRepository->findOneByValue($paymentDetails->getBlikAliasValue())
            : null;

        $this->do(
            fn () => $this->api->transactions()->createTransaction(
                $this->createBlikLevelZeroPaymentPayloadFactory->createFrom($model, $blikAlias, $notifyToken->getTargetUrl(), $localeCode),
            ),
            onSuccess: function (array $response) use ($paymentDetails) {
                $paymentDetails->setTransactionId($response['transactionId']);
                $paymentDetails->setStatus($response['status']);

                if (isset($response['payments']['errors'])) {
                    $paymentDetails->setStatus(PaymentInterface::STATE_FAILED);
                    $paymentDetails->setErrorMessage($response['payments']['errors'][0]['errorMessage'] ?? null);
                }
            },
            onFailure: function (array $response) use ($paymentDetails) {
                if (array_keys($response) !== ['payments']) {
                    $paymentDetails->setStatus(PaymentInterface::STATE_FAILED);
                }

                $this->handleErrors($response);
            },
            onUnknownOutcome: fn () => $paymentDetails->setStatus(PaymentInterface::STATE_PROCESSING),
        );
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

        return $paymentDetails->isBlik();
    }

    private function handleErrors(array $response): void
    {
        $responsePayments = $response['payments'] ?? [];
        $errors = $responsePayments['errors'] ?? [];

        if ([] === $errors) {
            return;
        }

        if (isset($responsePayments['alternatives'])) {
            throw BlikAliasAmbiguousValueException::create($responsePayments['alternatives']);
        }

        throw new \Exception('Unexpected error.');
    }
}
