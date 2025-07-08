<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Twig\Component;

use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayPolicy;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent]
final class PoliciesComponent
{
    private const POLISH_LOCALE = 'pl_PL';

    private const TPAY_PAYMENT_METHOD_PREFIX = 'tpay_';

    public ?PaymentMethodInterface $paymentMethod = null;

    public function __construct(
        private readonly LocaleContextInterface $localeContext,
    ) {
    }

    #[ExposeInTemplate(name: 'should_be_displayed')]
    public function shouldBeDisplayed(): bool
    {
        if ($this->paymentMethod === null) {
            return false;
        }

        return str_starts_with(
            $this->paymentMethod->getGatewayConfig()?->getFactoryName() ?? '',
            self::TPAY_PAYMENT_METHOD_PREFIX,
        );
    }

    #[ExposeInTemplate(name: 'tpay_regulations_url')]
    public function getTpayRegulationsUrl(): string
    {
        $localeCode = $this->localeContext->getLocaleCode();

        return $localeCode === self::POLISH_LOCALE ? TpayPolicy::REGULATIONS_PL : TpayPolicy::REGULATIONS_EN;
    }

    #[ExposeInTemplate(name: 'tpay_policy_url')]
    public function getTpayPolicyUrl(): string
    {
        $localeCode = $this->localeContext->getLocaleCode();

        return $localeCode === self::POLISH_LOCALE ? TpayPolicy::PAYER_INFORMATION_CLAUSE_PL : TpayPolicy::PAYER_INFORMATION_CLAUSE_EN;
    }
}
