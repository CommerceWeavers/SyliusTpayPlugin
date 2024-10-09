<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\CreateTransactionFactoryInterface;
use Payum\Core\Payum;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

abstract class AbstractPayByHandler
{
    public function __construct(
        protected readonly PaymentRepositoryInterface $paymentRepository,
        protected readonly Payum $payum,
        protected readonly CreateTransactionFactoryInterface $createTransactionFactory,
    ) {
    }

    protected function findOr404(int $paymentId): PaymentInterface
    {
        /** @var PaymentInterface|null $payment */
        $payment = $this->paymentRepository->find($paymentId);

        if (null === $payment) {
            throw new NotFoundHttpException(sprintf('Payment with id "%d" cannot be found.', $paymentId));
        }

        return $payment;
    }

    protected function createTransaction(PaymentInterface $payment): void
    {
        $gatewayName = $this->getGatewayName($payment);

        $this->payum->getGateway($gatewayName)->execute(
            $this->createTransactionFactory->createNewWithModel($payment),
            catchReply: true,
        );
    }

    protected function getGatewayName(PaymentInterface $payment): string
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $payment->getMethod();

        return $paymentMethod?->getGatewayConfig()?->getGatewayName() ?? throw new \InvalidArgumentException('Gateway name cannot be determined.');
    }

    protected function createResultFrom(PaymentInterface $payment, bool $isRedirected = true): PayResult
    {
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());

        Assert::notNull($paymentDetails->getStatus(), 'Payment status is required to create a result.');

        if ($isRedirected) {
            Assert::notNull($paymentDetails->getPaymentUrl(), 'Payment URL is required to create a result.');
        }

        // TODO factory?
        return new PayResult(
            $paymentDetails->getStatus(),
            $paymentDetails->getPaymentUrl(),
        );
    }
}
