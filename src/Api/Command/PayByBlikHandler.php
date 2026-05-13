<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Api\Enum\BlikAliasAction;
use CommerceWeavers\SyliusTpayPlugin\Api\Exception\BlikAliasAmbiguousValueException;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Entity\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Payum\Exception\BlikAliasAmbiguousValueException as PayumBlikAliasAmbiguousValueException;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard\ActiveBlikAliasPreconditionGuardInterface;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Resolver\BlikAliasResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Processor\CreateTransactionProcessorInterface;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PayByBlikHandler extends AbstractPayByHandler
{
    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        CreateTransactionProcessorInterface $createTransactionProcessor,
        private readonly BlikAliasResolverInterface $blikAliasResolver,
        private readonly ObjectManager $blikAliasManager,
        private readonly ActiveBlikAliasPreconditionGuardInterface $activeBlikAliasPreconditionGuard,
    ) {
        parent::__construct($paymentRepository, $createTransactionProcessor);
    }

    public function __invoke(PayByBlik $command): PayResult
    {
        $payment = $this->findOr404($command->paymentId);
        $blikAlias = null !== $command->blikAliasAction ? $this->resolveBlikAlias($payment) : null;

        if (null !== $blikAlias) {
            match ($command->blikAliasAction) {
                BlikAliasAction::APPLY => $this->activeBlikAliasPreconditionGuard->denyIfNotActive($blikAlias),
                BlikAliasAction::REGISTER => $blikAlias->redefine(),
            };
        }

        $this->setTransactionData($payment, $command, $blikAlias);

        try {
            $this->createTransactionProcessor->process($payment);
        } catch (PayumBlikAliasAmbiguousValueException $exception) {
            throw BlikAliasAmbiguousValueException::create($exception->getApplications());
        }

        if (null !== $blikAlias) {
            $this->blikAliasManager->persist($blikAlias);
        }

        return $this->createResultFrom($payment, isRedirectedBased: false);
    }

    private function setTransactionData(PaymentInterface $payment, PayByBlik $payByBlik, ?BlikAliasInterface $blikAlias): void
    {
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());
        $paymentDetails->setBlikToken($payByBlik->blikToken);
        $paymentDetails->setBlikAliasApplicationCode($payByBlik->blikAliasApplicationCode);
        $paymentDetails->setBlikAliasValue($blikAlias?->getValue());

        $payment->setDetails($paymentDetails->toArray());
    }

    private function resolveBlikAlias(PaymentInterface $payment): BlikAliasInterface
    {
        /** @var CustomerInterface $customer */
        $customer = $payment->getOrder()?->getCustomer() ?? throw new \InvalidArgumentException('The customer is missing.');

        return $this->blikAliasResolver->resolve($customer);
    }
}
