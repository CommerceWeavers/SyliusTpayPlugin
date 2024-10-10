<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PayByLinkHandler extends AbstractPayByHandler
{
    public function __invoke(PayByLink $command): PayResult
    {
        $payment = $this->findOr404($command->paymentId);

        $this->setTransactionData($payment, $command->tpayChannelId);
        $this->createTransaction($payment);

        return $this->createResultFrom($payment);
    }

    private function setTransactionData(PaymentInterface $payment, string $tpayChannelId): void
    {
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());
        $paymentDetails->setTpayChannelId($tpayChannelId);

        $payment->setDetails($paymentDetails->toArray());
    }
}
