<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByBlik;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\InitializeApplePayPaymentAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\InitializeApplePayPayment;
use CommerceWeavers\SyliusTpayPlugin\Tpay\ApplePayApi;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateInitializeApplePayPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Tests\CommerceWeavers\SyliusTpayPlugin\Helper\PaymentDetailsHelperTrait;
use Tpay\OpenApi\Utilities\TpayException;

final class InitializeApplePayPaymentActionTest extends TestCase
{
    use ProphecyTrait;

    use PaymentDetailsHelperTrait;

    private CreateInitializeApplePayPaymentPayloadFactoryInterface|ObjectProphecy $createInitializeApplePayPaymentPayloadFactory;

    private TpayApi|ObjectProphecy $api;

    protected function setUp(): void
    {
        $this->createInitializeApplePayPaymentPayloadFactory = $this->prophesize(CreateInitializeApplePayPaymentPayloadFactoryInterface::class);
        $this->api = $this->prophesize(TpayApi::class);
    }

    public function test_it_supports_only_initialize_apple_pay_payments(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new PayByBlik(1, 'test')));
        $this->assertTrue($action->supports(new InitializeApplePayPayment(new ArrayObject(), new ArrayObject())));
    }

    public function test_it_initializes_apple_pay_payment(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('en_US');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails(
            $this->getExpectedDetails(apple_pay_session: 'apple-pay-session'),
        )->shouldBeCalled();

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request = $this->prophesize(InitializeApplePayPayment::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);
        $request->getDomainName()->willReturn('cw.nonexisting');
        $request->getDisplayName()->willReturn('Commerce Weavers');
        $request->getValidationUrl()->willReturn('https://cw.nonexisting/validate');

        $this->createInitializeApplePayPaymentPayloadFactory->create(Argument::that(function (ArrayObject $data): bool {
            return $data['domainName'] === 'cw.nonexisting' &&
                $data['displayName'] === 'Commerce Weavers' &&
                $data['validationUrl'] === 'https://cw.nonexisting/validate'
            ;
        }))->willReturn([
            'domainName' => 'cw.nonexisting',
            'displayName' => 'Commerce Weavers',
            'validationUrl' => 'https://cw.nonexisting/validate',
        ]);

        $applePayApi = $this->prophesize(ApplePayApi::class);
        $applePayApi->init([
            'domainName' => 'cw.nonexisting',
            'displayName' => 'Commerce Weavers',
            'validationUrl' => 'https://cw.nonexisting/validate',
        ])->willReturn(['result' => 'success', 'session' => 'apple-pay-session']);

        $this->api->applePay()->willReturn($applePayApi);

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_marks_payment_as_failed_if_tpay_throws_an_exception(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('en_US');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails(
            $this->getExpectedDetails(status: 'failed'),
        )->shouldBeCalled();

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request = $this->prophesize(InitializeApplePayPayment::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);
        $request->getDomainName()->willReturn('cw.nonexisting');
        $request->getDisplayName()->willReturn('Commerce Weavers');
        $request->getValidationUrl()->willReturn('https://cw.nonexisting/validate');

        $this->createInitializeApplePayPaymentPayloadFactory->create(Argument::that(function (ArrayObject $data): bool {
            return $data['domainName'] === 'cw.nonexisting' &&
                $data['displayName'] === 'Commerce Weavers' &&
                $data['validationUrl'] === 'https://cw.nonexisting/validate'
                ;
        }))->willReturn([
            'domainName' => 'cw.nonexisting',
            'displayName' => 'Commerce Weavers',
            'validationUrl' => 'https://cw.nonexisting/validate',
        ]);

        $applePayApi = $this->prophesize(ApplePayApi::class);
        $applePayApi->init([
            'domainName' => 'cw.nonexisting',
            'displayName' => 'Commerce Weavers',
            'validationUrl' => 'https://cw.nonexisting/validate',
        ])->willThrow(new TpayException('Something went wrong'));

        $this->api->applePay()->willReturn($applePayApi);

        $this->createTestSubject()->execute($request->reveal());
    }

    private function createTestSubject(): InitializeApplePayPaymentAction
    {
        $action = new InitializeApplePayPaymentAction($this->createInitializeApplePayPaymentPayloadFactory->reveal());

        $action->setApi($this->api->reveal());

        return $action;
    }
}
