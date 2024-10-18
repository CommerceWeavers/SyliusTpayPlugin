<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\InitializeApplePayPaymentAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\InitializeApplePayPayment;
use CommerceWeavers\SyliusTpayPlugin\Tpay\ApplePayApi;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Bridge\Spl\ArrayObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class InitializeApplePayPaymentActionTest extends TestCase
{
    use ProphecyTrait;

    private TpayApi|ObjectProphecy $api;

    protected function setUp(): void
    {
        $this->api = $this->prophesize(TpayApi::class);
    }

    public function test_it_initializes_apple_pay_payment(): void
    {
        $applePayApi = $this->prophesize(ApplePayApi::class);
        $applePayApi->init([
            'domainName' => 'example.com',
            'displayName' => 'Example',
            'validationUrl' => 'https://example.com/apple-pay-validation-url',
        ])->shouldBeCalled()->willReturn(['result' => 'success']);

        $this->api->applePay()->willReturn($applePayApi);

        $model = new ArrayObject([
            'domainName' => 'example.com',
            'displayName' => 'Example',
            'validationUrl' => 'https://example.com/apple-pay-validation-url',
        ]);
        $output = new ArrayObject();

        $this->createTestSubject()->execute(new InitializeApplePayPayment($model, $output));

        $this->assertSame(['result' => 'success'], $output->getArrayCopy());
    }

    private function createTestSubject(): InitializeApplePayPaymentAction
    {
        $action = new InitializeApplePayPaymentAction();

        $action->setApi($this->api->reveal());

        return $action;
    }
}
