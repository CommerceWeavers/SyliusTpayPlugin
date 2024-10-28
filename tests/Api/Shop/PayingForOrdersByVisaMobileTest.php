<?php

declare(strict_types=1);

namespace Api\Shop;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\JsonApiTestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\Utils\CardEncrypterTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\Utils\OrderPlacerTrait;

final class PayingForOrdersByVisaMobileTest extends JsonApiTestCase
{
    use CardEncrypterTrait;
    use OrderPlacerTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpOrderPlacer();
    }

    public function test_it_returns_violation_if_phone_number_is_null(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_card');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_visa_mobile');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'visaMobilePhoneNumber' => null,
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'visaMobilePhoneNumber',
                'message' => 'The mobile phone number is required.',
            ],
        ]);
    }

    public function test_it_returns_violations_if_phone_number_is_empty(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_card');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_visa_mobile');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'visaMobilePhoneNumber' => '',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'visaMobilePhoneNumber',
                'message' => 'The mobile phone number is required.',
            ],
            [
                'propertyPath' => 'visaMobilePhoneNumber',
                'message' => 'The mobile phone must be composed minimum of 7 digits.',
            ],
        ]);
    }

    public function test_it_returns_violation_if_phone_number_is_too_short(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_card');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_visa_mobile');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'visaMobilePhoneNumber' => '123',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'visaMobilePhoneNumber',
                'message' => 'The mobile phone must be composed minimum of 7 digits.',
            ],
        ]);
    }

    public function test_it_returns_violation_if_phone_number_is_too_long(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_card');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_visa_mobile');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'visaMobilePhoneNumber' => '1234567890123456789',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'visaMobilePhoneNumber',
                'message' => 'The mobile phone must be composed maximum of 15 digits.',
            ],
        ]);
    }

    public function test_it_returns_violation_if_phone_number_is_not_composed_of_digits_only(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_card');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_visa_mobile');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'visaMobilePhoneNumber' => '+123aws12',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'visaMobilePhoneNumber',
                'message' => 'The mobile phone must consist only of digits.',
            ],
        ]);
    }

    public function test_paying_with_visa_mobile_based_payment_type(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_card');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_visa_mobile');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'visaMobilePhoneNumber' => '123456789',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);
        $this->assertResponse($response, 'shop/paying_for_orders_by_visa_mobile/test_paying_with_visa_mobile_payment_type');
    }

    public function test_it_handles_tpay_error_while_paying_with_visa_mobile_based_payment_type(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_card');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_visa_mobile');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'visaMobilePhoneNumber' => '00123789456',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, 424);
        $this->assertStringContainsString(
            'An error occurred while processing your payment. Please try again or contact store support.',
            $response->getContent(),
        );
    }
}
