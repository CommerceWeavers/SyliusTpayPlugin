<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\ContextProvider;

use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayPolicy;
use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Component\Locale\Context\LocaleContextInterface;

final class RegulationsUrlContextProvider
//    implements ContextProviderInterface
{
    public function __construct(
//        private readonly LocaleContextInterface $localeContext,
    ) {
    }

//    public function provide(array $templateContext, TemplateBlock $templateBlock): array
//    {
//        $localeCode = $this->localeContext->getLocaleCode();
//
//        if ($localeCode === 'pl_PL') {
//            $templateContext['tpayRegulationsUrl'] = TpayPolicy::REGULATIONS_PL;
//            $templateContext['tpayPolicyUrl'] = TpayPolicy::PAYER_INFORMATION_CLAUSE_PL;
//
//            return $templateContext;
//        }
//
//        $templateContext['tpayRegulationsUrl'] = TpayPolicy::REGULATIONS_EN;
//        $templateContext['tpayPolicyUrl'] = TpayPolicy::PAYER_INFORMATION_CLAUSE_EN;
//
//        return $templateContext;
//    }

//    public function supports(TemplateBlock $templateBlock): bool
//    {
//        return
//            'sylius.shop.checkout.complete.summary' === $templateBlock->getEventName() ||
//            'cw.tpay.shop.select_payment.choice_item_form' === $templateBlock->getEventName()
//        ;
//    }
}
