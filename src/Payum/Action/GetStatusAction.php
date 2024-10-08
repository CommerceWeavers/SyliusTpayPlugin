<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Payment\Model\PaymentInterface;

final class GetStatusAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param GetStatus $request
     */
    public function execute($request): void
    {
        /** @var PaymentInterface $model */
        $model = $request->getFirstModel();
        $paymentDetails = PaymentDetails::fromArray($model->getDetails());

        switch ($paymentDetails->getStatus()) {
            case 'correct':
            case PaymentInterface::STATE_COMPLETED:
                $request->markCaptured();

                break;
            case 'pending':
            case PaymentInterface::STATE_PROCESSING:
                $request->markPending();

                break;
            case 'refund':
            case PaymentInterface::STATE_REFUNDED:
                $request->markRefunded();

                break;
            case 'failed':
            case PaymentInterface::STATE_FAILED:
                $request->markFailed();

                break;
        }
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatus && $request->getFirstModel() instanceof PaymentInterface;
    }
}
