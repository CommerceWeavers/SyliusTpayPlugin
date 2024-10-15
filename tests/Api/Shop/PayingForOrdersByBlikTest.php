<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Api\Shop;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\JsonApiTestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\Utils\OrderPlacerTrait;

final class PayingForOrdersByBlikTest extends JsonApiTestCase
{
    use OrderPlacerTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpOrderPlacer();
    }

    public function test_paying_with_a_valid_blik_token_for_an_order(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_blik');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikToken' => '777123',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);
        $this->assertResponse($response, 'shop/paying_for_orders_by_blik/test_paying_with_a_valid_blik_token_for_an_order');
    }

    public function test_paying_with_a_valid_blik_token_and_saving_alias(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_blik');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikToken' => '777123',
                'blikSaveAlias' => true,
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);
        $this->assertResponse($response, 'shop/paying_for_orders_by_blik/test_paying_with_a_valid_blik_token_and_saving_alias');
    }

    public function test_paying_using_a_valid_blik_alias(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_blik');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikAlias' => 'myuniqalias',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);
        $this->assertResponse($response, 'shop/paying_for_orders_by_blik/test_paying_with_a_valid_blik_token_and_saving_alias');
    }

    public function test_paying_using_a_valid_blik_alias_registered_in_different_banks(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_blik');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikAlias' => 'mynonuniqalias',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertResponse($response, 'shop/paying_for_orders_by_blik/test_paying_using_a_valid_blik_alias_registered_in_different_alternatives');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikAlias' => 'mynonuniqalias',
                'blikApplicationCode' => '1ec8fe63-ea6e-6b48-ac6f-f7f170888d37',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);
        $this->assertResponse($response, 'shop/paying_for_orders_by_blik/test_paying_using_a_valid_blik_alias_registered_in_different_banks');
    }

    public function test_paying_with_a_too_short_blik_token(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_blik');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikToken' => '77712',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'blikToken',
                'message' => 'The BLIK token must have exactly 6 characters.',
            ]
        ]);
    }

    public function test_paying_with_a_too_long_blik_token(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_blik');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikToken' => '7771234',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'blikToken',
                'message' => 'The BLIK token must have exactly 6 characters.',
            ]
        ]);
    }

    public function test_paying_without_providing_a_blik_token(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_blik');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

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
                'propertyPath' => 'blikToken',
                'message' => 'The BLIK token is required.',
            ]
        ]);
    }

    private function doPlaceOrder(
        string $tokenValue,
        string $email = 'sylius@example.com',
        string $productVariantCode = 'MUG_BLUE',
        string $shippingMethodCode = 'UPS',
        string $paymentMethodCode = 'tpay',
        int $quantity = 1,
        ?\DateTimeImmutable $checkoutCompletedAt = null,

    ): OrderInterface {
        $this->checkSetUpOrderPlacerCalled();

        $this->pickUpCart($tokenValue);
        $this->addItemToCart($productVariantCode, $quantity, $tokenValue);
        $cart = $this->updateCartWithAddressAndCouponCode($tokenValue, $email);
        $this->dispatchShippingMethodChooseCommand(
            $tokenValue,
            $shippingMethodCode,
            (string)$cart->getShipments()->first()->getId(),
        );
        $this->dispatchPaymentMethodChooseCommand(
            $tokenValue,
            $paymentMethodCode,
            (string)$cart->getLastPayment()->getId(),
        );

        $order = $this->dispatchCompleteOrderCommand($tokenValue);

        $this->setCheckoutCompletedAt($order, $checkoutCompletedAt);

        return $order;
    }
}
