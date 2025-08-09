<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Customer\Resolver\IpAddressResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Customer\Resolver\UserAgentResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Routing\Generator\CallbackUrlGeneratorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final readonly class CreateRedirectBasedPaymentPayloadFactory implements CreateRedirectBasedPaymentPayloadFactoryInterface
{
    public function __construct(
        private CallbackUrlGeneratorInterface $callbackUrlGenerator,
        private TranslatorInterface $translator,
        private IpAddressResolverInterface $ipAddressResolver,
        private UserAgentResolverInterface $userAgentResolver,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createFrom(PaymentInterface $payment, string $notifyUrl, string $localeCode): array
    {
        $order = $payment->getOrder();
        Assert::notNull($order);
        $amount = $payment->getAmount();
        Assert::notNull($amount);

        return [
            'amount' => number_format($amount / 100, 2, thousands_separator: ''),
            'description' => $this->translator->trans(
                'commerce_weavers_sylius_tpay.tpay.transaction_description',
                ['%orderNumber%' => $order->getNumber()],
            ),
            'lang' => substr($localeCode, 0, 2),
            'payer' => $this->createPayerPayload($order),
            'callbacks' => [
                'payerUrls' => [
                    'success' => $this->callbackUrlGenerator->generateSuccessUrl($payment, $localeCode),
                    'error' => $this->callbackUrlGenerator->generateFailureUrl($payment, $localeCode),
                ],
                'notification' => [
                    'url' => $notifyUrl,
                ],
            ],
        ];
    }

    private function createPayerPayload(OrderInterface $order): array
    {
        $customer = $order->getCustomer();
        Assert::notNull($customer);
        $billingAddress = $order->getBillingAddress();
        Assert::notNull($billingAddress);

        $requiredResult = [
            'email' => $customer->getEmail(),
            'name' => $billingAddress->getFullName(),
        ];

        $result = [
            'phone' => $billingAddress->getPhoneNumber() ?? $customer->getPhoneNumber() ?? '',
            'address' => $billingAddress->getStreet() ?? '',
            'city' => $billingAddress->getCity() ?? '',
            'code' => $billingAddress->getPostcode() ?? '',
            'country' => $billingAddress->getCountryCode() ?? '',
            'ip' => $this->ipAddressResolver->resolve(),
            'useragent' => $this->userAgentResolver->resolve(),
        ];

        $result = array_filter($result, static function (string $value) {
            return $value !== '';
        });

        return array_merge($requiredResult, $result);
    }
}
