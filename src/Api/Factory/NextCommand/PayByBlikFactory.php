<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByBlik;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnsupportedNextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class PayByBlikFactory implements NextCommandFactoryInterface
{
    public function create(Pay $command, PaymentInterface $payment): PayByBlik
    {
        if (!$this->supports($command, $payment)) {
            throw new UnsupportedNextCommandFactory('This factory does not support the given command.');
        }

        return new PayByBlik((int) $payment->getId(), $command->blikToken, $command->blikSaveAlias, $command->blikUseAlias);
    }

    public function supports(Pay $command, PaymentInterface $payment): bool
    {
        return ($command->blikToken !== null || $command->blikUseAlias) && $payment->getId() !== null;
    }
}
