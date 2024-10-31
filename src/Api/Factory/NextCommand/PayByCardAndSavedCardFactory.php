<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnsupportedNextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class PayByCardAndSavedCardFactory implements NextCommandFactoryInterface
{
    public function create(Pay $command, PaymentInterface $payment): object
    {
        if (!$this->supports($command, $payment)) {
            throw new UnsupportedNextCommandFactory('This factory does not support the given command.');
        }

        throw new UnsupportedNextCommandFactory('Saved card UID and encoded card data cannot be used together.');
    }

    public function supports(Pay $command, PaymentInterface $payment): bool
    {
        return $command->encodedCardData !== null && $command->savedCardId !== null && $payment->getId() !== null;
    }
}
