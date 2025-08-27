<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Processor;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Exception\PaymentFailedException;
use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\CreateTransactionFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class CreateTransactionProcessor implements CreateTransactionProcessorInterface
{
    public function __construct(
        private readonly Payum $payum,
        private readonly CreateTransactionFactoryInterface $createTransactionFactory,
        private readonly GetStatusFactoryInterface $getStatusFactory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function process(PaymentInterface $payment): void
    {
        $this->createTransaction($payment);
        $this->refreshPayment($payment);

        if ($payment->getState() === PaymentInterface::STATE_FAILED) {
            $this->handlePaymentFailure($payment);
        }
    }

    private function createTransaction(PaymentInterface $payment): void
    {
        $this->getGatewayFor($payment)->execute(
            $this->createTransactionFactory->createNewWithModel($payment),
            catchReply: true,
        );
    }

    private function refreshPayment(PaymentInterface $payment): void
    {
        $this->getGatewayFor($payment)->execute(
            $this->getStatusFactory->createNewWithModel($payment),
            catchReply: true,
        );
    }

    /**
     * @throws PaymentFailedException
     */
    private function handlePaymentFailure(PaymentInterface $payment): void
    {
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());

        throw new PaymentFailedException(
            $paymentDetails->getErrorMessage() ?? $this->translator->trans('commerce_weavers_sylius_tpay.shop.payment_failed.error'),
        );
    }

    private function getGatewayFor(PaymentInterface $payment): GatewayInterface
    {
        return $this->payum->getGateway($this->extractGatewayNameFrom($payment));
    }

    private function extractGatewayNameFrom(PaymentInterface $payment): string
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $payment->getMethod();
        $gatewayName = $paymentMethod?->getGatewayConfig()?->getFactoryName();

        Assert::notNull($gatewayName);

        return $gatewayName;
    }
}
