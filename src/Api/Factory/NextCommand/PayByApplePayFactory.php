<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByApplePay;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnsupportedNextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class PayByApplePayFactory implements NextCommandFactoryInterface
{
    public function create(Pay $command, PaymentInterface $payment): PayByApplePay
    {
        if (!$this->supports($command, $payment)) {
            throw new UnsupportedNextCommandFactory('This factory does not support the given command.');
        }

        /** @var int $paymentId */
        $paymentId = $payment->getId();
        /** @var string $applePayToken */
        $applePayToken = $command->applePayToken;

        return new PayByApplePay($paymentId, $applePayToken);
    }

    public function supports(Pay $command, PaymentInterface $payment): bool
    {
        return $command->applePayToken !== null && $payment->getId() !== null;
    }
}
