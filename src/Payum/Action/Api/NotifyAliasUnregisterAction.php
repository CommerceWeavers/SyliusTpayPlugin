<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\NotifyAliasUnregister;
use CommerceWeavers\SyliusTpayPlugin\Repository\BlikAliasRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Reply\HttpResponse;
use Sylius\Component\Core\Model\PaymentInterface;

final class NotifyAliasUnregisterAction implements ActionInterface
{
    public function __construct(
        private readonly BlikAliasRepositoryInterface $blikAliasRepository,
        private readonly ObjectManager $blikAliasManager,
    ) {
    }

    /**
     * @param NotifyAliasUnregister $request
     */
    public function execute($request): void
    {
        $data = $request->getData();
        /** @var array{msg_value?: array{value?: string, expirationDate?: string}} $content */
        $content = json_decode($data->requestContent, true);
        $msgValue = $content['msg_value'] ?? throw new \InvalidArgumentException('The msg_value is missing.');
        $aliasValue = $msgValue['value'] ?? throw new \InvalidArgumentException('The msg_value.value is missing.');

        $blikAlias = $this->blikAliasRepository->findOneByValue($aliasValue);
        if (null === $blikAlias) {
            throw new HttpResponse('FALSE - Alias not found', 400);
        }

        $this->blikAliasManager->remove($blikAlias);

        throw new HttpResponse('TRUE', 200);
    }

    public function supports($request): bool
    {
        return $request instanceof NotifyAliasUnregister && $request->getModel() instanceof PaymentInterface;
    }
}
