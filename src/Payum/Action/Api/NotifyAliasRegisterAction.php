<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\NotifyAliasRegister;
use CommerceWeavers\SyliusTpayPlugin\Resolver\BlikAliasResolverInterface;
use Doctrine\Persistence\ObjectManager;
use Payum\Core\Action\ActionInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class NotifyAliasRegisterAction implements ActionInterface
{
    public function __construct(
        private readonly BlikAliasResolverInterface $blikAliasResolver,
        private readonly ObjectManager $objectManager,
    ) {
    }

    /**
     * @param NotifyAliasRegister $request
     */
    public function execute($request): void
    {
        /** @var PaymentInterface $model */
        $model = $request->getModel();
        $data = $request->getData();
        $content = json_decode($data->requestContent, true);

        /** @var array{msg_value?: array{value?: string, expirationDate?: string}} $content */
        $msgValue = $content['msg_value'] ?? throw new \InvalidArgumentException('The msg_value is missing.');
        $aliasValue = $msgValue['value'] ?? throw new \InvalidArgumentException('The msg_value.value is missing.');
        $aliasExpirationDate = $msgValue['expirationDate'] ?? throw new \InvalidArgumentException('The msg_value.expirationDate is missing.');

        /** @var CustomerInterface $customer */
        $customer = $model->getOrder()?->getCustomer() ?? throw new \InvalidArgumentException('The customer is missing.');

        $blikAlias = $this->blikAliasResolver->resolve($customer);
        $blikAlias->setValue($aliasValue);
        $blikAlias->setExpirationDate(new \DateTimeImmutable($aliasExpirationDate));

        $this->objectManager->persist($blikAlias);
    }

    public function supports($request): bool
    {
        return $request instanceof NotifyAliasRegister && $request->getModel() instanceof PaymentInterface;
    }
}
