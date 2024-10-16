<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final class PayByBlikHandler extends AbstractPayByHandler
{
    public function __invoke(PayByBlik $command): PayResult
    {
        $payment = $this->findOr404($command->paymentId);

        $this->setTransactionData($payment, $command);
        $this->createTransaction($payment);

        return $this->createResultFrom($payment);
    }

    private function setTransactionData(PaymentInterface $payment, PayByBlik $payByBlik): void
    {
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());
        $paymentDetails->setBlikToken($payByBlik->blikToken);
        $paymentDetails->setBlikSaveAlias($payByBlik->blikSaveAlias);

        $payment->setDetails($paymentDetails->toArray());
    }

    private function createResultFrom(PaymentInterface $payment): PayResult
    {
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());

        Assert::notNull($paymentDetails->getStatus(), 'Payment status is required to create a result.');

        return new PayResult($paymentDetails->getStatus());
    }
}
