<?php

declare(strict_types=1);

namespace Api\Shop;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\JsonApiTestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\Utils\CardEncrypterTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\Api\Utils\OrderPlacerTrait;

class PayingForOrdersByPblTest extends JsonApiTestCase
{
    use CardEncrypterTrait;
    use OrderPlacerTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpOrderPlacer();
    }

    public function test_it_returns_violation_if_bank_is_not_available(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_card');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_pbl');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'tpayChannelId' => '3'
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'tpayChannelId',
                'message' => 'Channel with provided id is not available.',
            ]
        ]);
    }

    public function test_it_returns_violation_if_channel_is_not_a_bank(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_card');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_pbl');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'tpayChannelId' => '2'
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'tpayChannelId',
                'message' => 'Channel with provided id is not a bank.',
            ]
        ]);
    }

    public function test_paying_with_pbl_based_payment_type(): void
    {
        $this->loadFixturesFromDirectory('shop/paying_for_orders_by_card');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_pbl');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'tpayChannelId' => '1'
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);
        $this->assertResponse($response, 'shop/paying_for_orders_by_pbl/test_paying_with_pay_by_link_based_payment_type');
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
