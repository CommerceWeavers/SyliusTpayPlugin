<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class CreateRedirectBasedPaymentPayloadFactory implements CreateRedirectBasedPaymentPayloadFactoryInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly string $successRoute,
        private readonly string $errorRoute,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createFrom(PaymentInterface $payment, string $notifyUrl, string $localeCode): array
    {
        $order = $payment->getOrder();
        Assert::notNull($order);
        $customer = $order->getCustomer();
        Assert::notNull($customer);
        $billingAddress = $order->getBillingAddress();
        Assert::notNull($billingAddress);
        $amount = $payment->getAmount();
        Assert::notNull($amount);

        return [
            'amount' => number_format($amount / 100, 2, thousands_separator: ''),
            'description' => $this->translator->trans(
                'commerce_weavers_sylius_tpay.tpay.transaction_description',
                ['%orderNumber%' => $order->getNumber()],
            ),
            'payer' => [
                'email' => $customer->getEmail(),
                'name' => $billingAddress->getFullName(),
            ],
            'callbacks' => [
                'payerUrls' => [
                    'success' => $this->getSuccessUrl($payment, $localeCode),
                    'error' => $this->getFailureUrl($payment, $localeCode),
                ],
                'notification' => [
                    'url' => $notifyUrl,
                ],
            ],
        ];
    }

    private function getSuccessUrl(PaymentInterface $payment, string $localeCode): string
    {
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());

        if ($paymentDetails->getSuccessUrl() === null) {
            return $this->router->generate(
                $this->successRoute,
                ['_locale' => $localeCode],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
        }

        return $paymentDetails->getSuccessUrl();
    }

    private function getFailureUrl(PaymentInterface $payment, string $localeCode): string
    {
        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());

        if ($paymentDetails->getFailureUrl() === null) {
            return $this->router->generate(
                $this->errorRoute,
                ['_locale' => $localeCode],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
        }

        return $paymentDetails->getFailureUrl();
    }
}
