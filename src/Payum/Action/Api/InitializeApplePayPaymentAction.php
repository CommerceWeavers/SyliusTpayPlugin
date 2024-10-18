<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\InitializeApplePayPayment;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Tpay\OpenApi\Utilities\TpayException;

final class InitializeApplePayPaymentAction extends AbstractCreateTransactionAction
{
    use GenericTokenFactoryAwareTrait;

    /**
     * @param InitializeApplePayPayment $request
     *
     * @throws TpayException
     */
    public function execute($request): void
    {
        /** @var ArrayObject $model */
        $model = $request->getModel();

        /** @var array<string, mixed> $result */
        $result = $this->api->applePay()->init([
            'domainName' => $model['domainName'],
            'displayName' => $model['displayName'],
            'validationUrl' => $model['validationUrl'],
        ]);

        $request->getOutput()->replace($result);
    }

    public function supports($request): bool
    {
        return $request instanceof InitializeApplePayPayment;
    }
}
