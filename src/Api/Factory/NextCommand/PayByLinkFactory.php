<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByLink;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnsupportedNextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class PayByLinkFactory implements NextCommandFactoryInterface
{
    public function create(Pay $command, PaymentInterface $payment): object
    {
        if (!$this->supports($command, $payment)) {
            throw new UnsupportedNextCommandFactory('This factory does not support the given command.');
        }

        /** @var int $paymentId */
        $paymentId = $payment->getId();
        /** @var string $payByLinkChannelId */
        $payByLinkChannelId = $command->tpayChannelId;

        return new PayByLink($paymentId, $payByLinkChannelId);
    }

    public function supports(Pay $command, PaymentInterface $payment): bool
    {
        return $command->tpayChannelId !== null && $payment->getId() !== null;
    }
}
