<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\ContextProvider;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;
use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;

final class BankListContextProvider implements ContextProviderInterface
{
    public function __construct(
        private readonly TpayApiBankListProviderInterface $bankListProvider,
    ) {
    }

    public function provide(array $templateContext, TemplateBlock $templateBlock): array
    {
        $templateContext['banks'] = $this->bankListProvider->provide();

        return $templateContext;
    }

    public function supports(TemplateBlock $templateBlock): bool
    {
        return 'sylius.shop.checkout.complete.summary' === $templateBlock->getEventName() &&
            'pay_by_link' === $templateBlock->getName()
        ;
    }
}
