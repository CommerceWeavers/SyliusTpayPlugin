<?php

declare(strict_types=1);

namespace Api\Shop;

use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayConfigTypeEquals;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\JsonApiTestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\Utils\OrderPlacerTrait;

final class PayingForOrdersByApplePayTest extends JsonApiTestCase
{
    use OrderPlacerTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpOrderPlacer();

        $this->loadFixturesFromFile('shop/paying_for_orders_by_apple_pay.yml');
    }

    public function test_it_initializes_an_apple_pay_session(): void
    {
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_apple_pay');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/apple-pay-session', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'domainName' => 'cw.nonexisting',
                'displayName' => 'Commerce Weavers',
                'validationUrl' => 'https://cw.nonexisting/validation',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponse(
            $response,
            'shop/paying_for_orders_by_apple_pay/test_initializing_an_apple_pay_session',
            Response::HTTP_CREATED,
        );
    }

    public function test_paying_with_a_valid_apple_pay_token_for_an_order(): void
    {
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_apple_pay');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'applePayToken' => 'apple-pay-token',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);
        $this->assertResponse($response, 'shop/paying_for_orders_by_apple_pay/test_paying_with_a_valid_apple_pay_token_for_an_order');
    }

    public function test_paying_without_providing_a_apple_pay_token(): void
    {
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_apple_pay');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'applePayToken',
                'code' => NotBlankIfGatewayConfigTypeEquals::FIELD_REQUIRED_ERROR,
                'message' => 'The Apple Pay token is required.',
            ]
        ]);
    }
}
