<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PayByRedirectHandler extends AbstractPayByHandler
{
    public function __invoke(PayByRedirect $command): PayResult
    {
        $payment = $this->findOr404($command->paymentId);

        $this->createTransaction($payment);

        return $this->createResultFrom($payment);
    }
}
