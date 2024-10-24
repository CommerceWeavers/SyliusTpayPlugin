<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Entity\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\CreateTransactionFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Resolver\BlikAliasResolverInterface;
use Doctrine\Persistence\ObjectManager;
use Payum\Core\Payum;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PayByBlikHandler extends AbstractPayByHandler
{
    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        Payum $payum,
        CreateTransactionFactoryInterface $createTransactionFactory,
        private readonly BlikAliasResolverInterface $blikAliasResolver,
        private readonly ObjectManager $blikAliasManager,
    ) {
        parent::__construct($paymentRepository, $payum, $createTransactionFactory);
    }

    public function __invoke(PayByBlik $command): PayResult
    {
        $payment = $this->findOr404($command->paymentId);
        $blikAlias = ($command->blikSaveAlias || $command->blikUseAlias) ? $this->resolveBlikAlias($payment) : null;

        if ($command->blikSaveAlias) {
            $blikAlias?->redefine();
        }

        $this->setTransactionData($payment, $command, $blikAlias);
        $this->createTransaction($payment);

        if (null !== $blikAlias) {
            $this->blikAliasManager->persist($blikAlias);
        }

        return $this->createResultFrom($payment, isRedirectedBased: false);
    }

    private function setTransactionData(PaymentInterface $payment, PayByBlik $payByBlik, ?BlikAliasInterface $blikAlias): void
    {
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());
        $paymentDetails->setBlikToken($payByBlik->blikToken);
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
