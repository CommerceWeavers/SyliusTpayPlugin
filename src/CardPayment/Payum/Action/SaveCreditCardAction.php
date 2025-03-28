<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\CardPayment\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\CardPayment\Entity\CreditCardInterface;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Payum\Request\Api\SaveCreditCard;
use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\BasePaymentAwareAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Generic;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class SaveCreditCardAction extends BasePaymentAwareAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private readonly FactoryInterface $creditCardFactory,
        private readonly RepositoryInterface $creditCardRepository,
    ) {
        parent::__construct();
    }

    /**
     * @param SaveCreditCard $request
     */
    protected function doExecute(Generic $request, PaymentInterface $model, PaymentDetails $paymentDetails, string $gatewayName, string $localeCode): void
    {
        /** @var CreditCardInterface $creditCard */
        $creditCard = $this->creditCardFactory->createNew();

        $creditCard->setToken($request->getCardToken());
        $creditCard->setBrand($request->getCardBrand());
        $creditCard->setTail($request->getCardTail());

        /** @var ?OrderInterface $order */
        $order = $model->getOrder();
        $customer = $order?->getCustomer();

        Assert::isInstanceOf($customer, CustomerInterface::class);

        $creditCard->setCustomer($customer);
        $creditCard->setChannel($order?->getChannel());

        $expiryDate = $request->getTokenExpiryDate();

        $creditCard->setExpirationDate(new \DateTimeImmutable(
            sprintf(
                '01-%s-20%s',
                substr($expiryDate, 0, 2),
                substr($expiryDate, 2, 2),
            ),
        ));

        $this->creditCardRepository->add($creditCard);
    }

    public function supports($request): bool
    {
        return $request instanceof SaveCreditCard && $request->getModel() instanceof PaymentInterface;
    }
}
