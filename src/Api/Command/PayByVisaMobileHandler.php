<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PayByVisaMobileHandler extends AbstractPayByHandler
{
    public function __invoke(PayByVisaMobile $command): PayResult
    {
        $payment = $this->findOr404($command->paymentId);

        $this->setTransactionData($payment);
        $this->createTransaction($payment);

        return $this->createResultFrom($payment, false);
    }

    private function setTransactionData(PaymentInterface $payment): void
    {
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());
        $paymentDetails->setVisaMobilePhoneNumber($paymentDetails->getVisaMobilePhoneNumber());

        $payment->setDetails($paymentDetails->toArray());
    }
}
