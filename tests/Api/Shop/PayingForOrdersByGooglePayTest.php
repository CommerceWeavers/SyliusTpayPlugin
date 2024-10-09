<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Api\Shop;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\JsonApiTestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\Utils\OrderPlacerTrait;

final class PayingForOrdersByGooglePayTest extends JsonApiTestCase
{
    use OrderPlacerTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpOrderPlacer();
    }

    public function test_paying_with_a_valid_google_pay_token_for_an_order(): void
    {
        $this->loadFixturesFromFile('shop/paying_for_orders_by_google_pay');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_google_apy');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'googlePayToken' => 'base64token',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);
        $this->assertResponse($response, 'shop/paying_for_orders_by_google_pay/test_paying_with_a_valid_token_for_an_order');
    }

    public function test_paying_with_not_encoded_google_pay_token(): void
    {
        $this->loadFixturesFromFile('shop/paying_for_orders_by_google_pay');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_google_pay');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'googlePayToken' => 'someInvalidValue',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'googlePayToken',
                'code' => 'c146928c-f22b-4802-ba90-5fb9952e7ee8',
                'message' => 'The Google Pay token must be a JSON object encoded with Base64.',
            ]
        ]);
    }

    public function test_paying_with_a_google_pay_token_that_is_not_a_json_object(): void
    {
        $this->loadFixturesFromFile('shop/paying_for_orders_by_google_pay');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_google_pay');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'googlePayToken' => 'base64invalidToken',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'googlePayToken',
                'code' => 'c146928c-f22b-4802-ba90-5fb9952e7ee8',
                'message' => 'The Google Pay token must be a JSON object encoded with Base64.',
            ]
        ]);
    }

    public function test_paying_without_providing_a_google_pay_token(): void
    {
        $this->loadFixturesFromFile('shop/paying_for_orders_by_google_pay');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_google_pay');

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
                'propertyPath' => 'googlePayToken',
                'code' => '275416a8-bd6f-4990-96ed-a2da514ce2f9',
                'message' => 'The Google Pay token is required.',
            ]
        ]);
    }
}
