<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreatePayByLinkPayloadFactoryInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class CreatePayByLinkTransactionAction extends AbstractCreateTransactionAction implements GatewayAwareInterface
{
    use GenericTokenFactoryAwareTrait;
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
        $details = $model->getDetails();
        $token = $request->getToken();
        Assert::notNull($token);

        $localeCode = $this->getLocaleCodeFrom($model);

        $notifyToken = $this->notifyTokenFactory->create($model, $token->getGatewayName(), $localeCode);

        $response = $this->api->transactions()->createTransaction(
            $this->createPayByLinkPayloadFactory->createFrom($model, $notifyToken->getTargetUrl(), $localeCode),
        );

        $details['tpay']['transaction_id'] = $response['transactionId'];
        $details['tpay']['status'] = $response['status'];
        $details['tpay']['transaction_payment_url'] = $response['transactionPaymentUrl'];

        $model->setDetails($details);

        throw new HttpRedirect($details['tpay']['transaction_payment_url']);
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

        return isset($details['tpay']['pay_by_link_channel_id']);
    }
}
