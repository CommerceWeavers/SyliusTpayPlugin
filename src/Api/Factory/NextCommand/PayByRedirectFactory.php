<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByRedirect;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnsupportedNextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\RedirectPayment\Payum\Factory\GatewayFactory;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class PayByRedirectFactory implements NextCommandFactoryInterface
{
    public function create(Pay $command, PaymentInterface $payment): PayByRedirect
    {
        if (!$this->supports($command, $payment)) {
            throw new UnsupportedNextCommandFactory('This factory does not support the given command.');
        }

        /** @var int $paymentId */
        $paymentId = $payment->getId();

        return new PayByRedirect($paymentId);
    }

    public function supports(Pay $command, PaymentInterface $payment): bool
    {
        if ($payment->getId() === null) {
            return false;
        }

        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $payment->getMethod();
        $gatewayName = $paymentMethod?->getGatewayConfig()?->getFactoryName();

        return $gatewayName === GatewayFactory::NAME;
    }
}
