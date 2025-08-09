<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Customer\Resolver\IpAddressResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Customer\Resolver\UserAgentResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Routing\Generator\CallbackUrlGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\InvalidArgumentException;

final class CreateRedirectBasedPaymentPayloadFactoryTest extends TestCase
{
    use ProphecyTrait;

    private const TRANSLATED_DESCRIPTION = 'Zamówienie #000000001';

    /** @var ObjectProphecy<CallbackUrlGeneratorInterface> */
    private ObjectProphecy $callbackUrlGenerator;

    /** @var ObjectProphecy<TranslatorInterface> */
    private ObjectProphecy $translator;

    /** @var ObjectProphecy<IpAddressResolverInterface> */
    private ObjectProphecy $ipAddressResolver;

    /** @var ObjectProphecy<UserAgentResolverInterface> */
    private ObjectProphecy $userAgentResolver;

    protected function setUp(): void
    {
        $this->callbackUrlGenerator = $this->prophesize(CallbackUrlGeneratorInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);

        $this->ipAddressResolver = $this->prophesize(IpAddressResolverInterface::class);
        $this->ipAddressResolver->resolve()->willReturn('127.0.0.1');

        $this->userAgentResolver = $this->prophesize(UserAgentResolverInterface::class);
        $this->userAgentResolver->resolve()->willReturn('Carrier Pigeon 3000');
    }

    public function test_it_returns_a_payload_for_a_redirect_based_payment(): void
    {
        $billingAddress = $this->prophesize(AddressInterface::class);
        $billingAddress->getFullName()->willReturn('Don Matteo');

        $customer = $this->prophesize(CustomerInterface::class);
        $customer->getEmail()->willReturn('don.matteo@sandomierz.org');

        $order = $this->prophesize(OrderInterface::class);
        $order->getCustomer()->willReturn($customer);
        $order->getBillingAddress()->willReturn($billingAddress);
        $order->getNumber()->willReturn('000000001');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(1050);

        $billingAddress->getPhoneNumber()->willReturn('123123123');
        $billingAddress->getStreet()->willReturn('Sesame Street');
        $billingAddress->getCity()->willReturn('Sesame City');
        $billingAddress->getPostcode()->willReturn('90 210');
        $billingAddress->getCountryCode()->willReturn('PL');

        $this->translator->trans(
            'commerce_weavers_sylius_tpay.tpay.transaction_description',
            ['%orderNumber%' => '000000001']
        )->willReturn(self::TRANSLATED_DESCRIPTION);

        $this->callbackUrlGenerator
            ->generateSuccessUrl($payment, 'pl_PL')
            ->willReturn('https://cw.org/success')
        ;
        $this->callbackUrlGenerator
            ->generateFailureUrl($payment, 'pl_PL')
            ->willReturn('https://cw.org/error')
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');

        $this->assertSame([
            'amount' => '10.50',
            'description' => self::TRANSLATED_DESCRIPTION,
            'lang' => 'pl',
            'payer' => [
                'email' => 'don.matteo@sandomierz.org',
                'name' => 'Don Matteo',
                'phone' => '123123123',
                'address' => 'Sesame Street',
                'city' => 'Sesame City',
                'code' => '90 210',
                'country' => 'PL',
                'ip' => '127.0.0.1',
                'useragent' => 'Carrier Pigeon 3000',
            ],
            'callbacks' => [
                'payerUrls' => [
                    'success' => 'https://cw.org/success',
                    'error' => 'https://cw.org/error',
                ],
                'notification' => [
                    'url' => 'https://cw.org/notify',
                ],
            ],
        ], $payload);
    }

    public function test_it_returns_a_payload_with_fields_that_have_value_only(): void
    {
        $billingAddress = $this->prophesize(AddressInterface::class);
        $billingAddress->getFullName()->willReturn('Don Matteo');

        $customer = $this->prophesize(CustomerInterface::class);
        $customer->getEmail()->willReturn('don.matteo@sandomierz.org');

        $order = $this->prophesize(OrderInterface::class);
        $order->getCustomer()->willReturn($customer);
        $order->getBillingAddress()->willReturn($billingAddress);
        $order->getNumber()->willReturn('000000001');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(1050);

        $billingAddress->getPhoneNumber()->willReturn('123123123');
        $billingAddress->getStreet()->willReturn('');
        $billingAddress->getCity()->willReturn('');
        $billingAddress->getPostcode()->willReturn('90 210');
        $billingAddress->getCountryCode()->willReturn('PL');

        $this->translator->trans(
            'commerce_weavers_sylius_tpay.tpay.transaction_description',
            ['%orderNumber%' => '000000001']
        )->willReturn(self::TRANSLATED_DESCRIPTION);

        $this->callbackUrlGenerator
            ->generateSuccessUrl($payment, 'pl_PL')
            ->willReturn('https://cw.org/success')
        ;
        $this->callbackUrlGenerator
            ->generateFailureUrl($payment, 'pl_PL')
            ->willReturn('https://cw.org/error')
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');

        $this->assertSame([
            'amount' => '10.50',
            'description' => self::TRANSLATED_DESCRIPTION,
            'lang' => 'pl',
            'payer' => [
                'email' => 'don.matteo@sandomierz.org',
                'name' => 'Don Matteo',
                'phone' => '123123123',
                'code' => '90 210',
                'country' => 'PL',
                'ip' => '127.0.0.1',
                'useragent' => 'Carrier Pigeon 3000',
            ],
            'callbacks' => [
                'payerUrls' => [
                    'success' => 'https://cw.org/success',
                    'error' => 'https://cw.org/error',
                ],
                'notification' => [
                    'url' => 'https://cw.org/notify',
                ],
            ],
        ], $payload);
    }

    public function test_it_always_returns_a_payload_with_required_fields_even_if_their_value_is_empty(): void
    {
        $billingAddress = $this->prophesize(AddressInterface::class);
        $billingAddress->getFullName()->willReturn('');

        $customer = $this->prophesize(CustomerInterface::class);
        $customer->getEmail()->willReturn('');

        $order = $this->prophesize(OrderInterface::class);
        $order->getCustomer()->willReturn($customer);
        $order->getBillingAddress()->willReturn($billingAddress);
        $order->getNumber()->willReturn('000000001');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(1050);

        $billingAddress->getPhoneNumber()->willReturn('123123123');
        $billingAddress->getStreet()->willReturn('Sesame Street');
        $billingAddress->getCity()->willReturn('Sesame City');
        $billingAddress->getPostcode()->willReturn('90 210');
        $billingAddress->getCountryCode()->willReturn('PL');

        $this->translator->trans(
            'commerce_weavers_sylius_tpay.tpay.transaction_description',
            ['%orderNumber%' => '000000001']
        )->willReturn(self::TRANSLATED_DESCRIPTION);

        $this->callbackUrlGenerator
            ->generateSuccessUrl($payment, 'pl_PL')
            ->willReturn('https://cw.org/success')
        ;
        $this->callbackUrlGenerator
            ->generateFailureUrl($payment, 'pl_PL')
            ->willReturn('https://cw.org/error')
        ;

        $payload = $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');

        $this->assertSame([
            'amount' => '10.50',
            'description' => self::TRANSLATED_DESCRIPTION,
            'lang' => 'pl',
            'payer' => [
                'email' => '',
                'name' => '',
                'phone' => '123123123',
                'address' => 'Sesame Street',
                'city' => 'Sesame City',
                'code' => '90 210',
                'country' => 'PL',
                'ip' => '127.0.0.1',
                'useragent' => 'Carrier Pigeon 3000',
            ],
            'callbacks' => [
                'payerUrls' => [
                    'success' => 'https://cw.org/success',
                    'error' => 'https://cw.org/error',
                ],
                'notification' => [
                    'url' => 'https://cw.org/notify',
                ],
            ],
        ], $payload);
    }

    public function test_it_throws_an_exception_if_the_order_is_null(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getAmount()->willReturn(1050);
        $payment->getOrder()->willReturn(null);

        $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');
    }


    public function test_it_throws_an_exception_if_the_customer_is_null(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $order = $this->prophesize(OrderInterface::class);
        $order->getCustomer()->willReturn(null);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getAmount()->willReturn(1050);
        $payment->getOrder()->willReturn(null);

        $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');
    }

    public function test_it_throws_an_exception_if_the_amount_is_null(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $billingAddress = $this->prophesize(AddressInterface::class);
        $customer = $this->prophesize(CustomerInterface::class);
        $order = $this->prophesize(OrderInterface::class);
        $order->getCustomer()->willReturn($customer);
        $order->getBillingAddress()->willReturn($billingAddress);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(null);

        $this->createTestSubject()->createFrom($payment->reveal(), 'https://cw.org/notify', 'pl_PL');
    }

    private function createTestSubject(): CreateRedirectBasedPaymentPayloadFactoryInterface
    {
        return new CreateRedirectBasedPaymentPayloadFactory(
            $this->callbackUrlGenerator->reveal(),
            $this->translator->reveal(),
            $this->ipAddressResolver->reveal(),
            $this->userAgentResolver->reveal(),
        );
    }
}
