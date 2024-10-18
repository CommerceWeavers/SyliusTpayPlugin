<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Controller;

use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\InitializeApplePayPayment;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class InitApplePayPaymentAction
{
    public function __construct(
        private readonly Payum $payum,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $gateway = $this->getGateway();

        $gateway->execute(new InitializeApplePayPayment(
            new ArrayObject([
                'domainName' => $request->request->get('domainName'),
                'displayName' => $request->request->get('displayName'),
                'validationUrl' => $request->request->get('validationUrl'),
            ]),
            $output = new ArrayObject(),
        ));

        return new JsonResponse($output->toUnsafeArray());
    }

    private function getGateway(): GatewayInterface
    {
        return $this->payum->getGateway('tpay');
    }
}
