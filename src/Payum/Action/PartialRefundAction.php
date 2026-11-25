<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\BaseApiAwareAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Exception\RefundCannotBeMadeException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Refund;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;
use Webmozart\Assert\Assert;

final class PartialRefundAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Refund $request
     */
    public function execute(mixed $request): void
    {
        /** @var RefundPaymentInterface $refundPayment */
        $refundPayment = $request->getModel();
        $payment = $this->extractPaymentFrom($refundPayment);
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());
        $transactionId = $paymentDetails->getTransactionId();

        if (null === $transactionId) {
            throw new RefundCannotBeMadeException('Tpay transaction id cannot be found.');
        }

        if ($refundPayment->getAmount() === $payment->getAmount()) {
            $this->gateway->execute(new Refund($payment));

            return;
        }

        $response = $this->api->transactions()->createRefundByTransactionId(
            ['amount' => $this->convertFromMinorToMajorCurrency($refundPayment->getAmount())],
            $transactionId,
        );

        if ('failed' === ($response['result'] ?? null)) {
            throw new RefundCannotBeMadeException($response['errors'][0]['errorMessage'] ?? 'Tpay rejected the refund.');
        }
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof Refund && $request->getModel() instanceof RefundPaymentInterface;
    }

    private function extractPaymentFrom(RefundPaymentInterface $refundPayment): PaymentInterface
    {
        $order = $refundPayment->getOrder();
        $payment = $order->getLastPayment();

        Assert::notNull($payment);

        return $payment;
    }

    private function convertFromMinorToMajorCurrency(int $amount): float
    {
        return $amount / 100;
    }
}
