<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Api\Shop;

use CommerceWeavers\SyliusTpayPlugin\Api\Enum\BlikAliasAction;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfBlikAliasActionIsRegister;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\OneOfPropertiesRequiredIfGatewayConfigTypeEquals;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Length;
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
        $this->loadFixturesFromFile('shop/blik_payment_method.yml');

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

        $this->assertResponse($response, 'shop/paying_for_orders_by_blik/test_paying_with_a_valid_blik_token_for_an_order');
    }

    public function test_it_handles_tpay_error_while_paying_with_blik_based_payment_type(): void
    {
        $this->loadFixturesFromFile('shop/blik_payment_method.yml');

        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikToken' => '999137',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, 424);
        $this->assertStringContainsString(
            'Podany kod jest nieprawidłowy, bądź utracił ważność.',
            $response->getContent(),
        );
    }

    public function test_paying_with_a_valid_blik_token_and_saving_alias(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['shop/blik_payment_method.yml', 'shop/common/shop_user.yml']);

        /** @var ShopUserInterface $shopUser */
        $shopUser = $fixtures['shop_user_john_doe'];
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER + $this->generateAuthorizationHeader($shopUser),
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikToken' => '777123',
                'blikAliasAction' => BlikAliasAction::REGISTER->value,
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponse($response, 'shop/paying_for_orders_by_blik/test_paying_with_a_valid_blik_token_for_an_order');
    }

    public function test_paying_and_saving_alias_without_a_blik_token(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['shop/blik_payment_method.yml', 'shop/common/shop_user.yml']);

        /** @var ShopUserInterface $shopUser */
        $shopUser = $fixtures['shop_user_john_doe'];
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER + $this->generateAuthorizationHeader($shopUser),
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikAliasAction' => BlikAliasAction::REGISTER->value,
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'blikToken',
                'code' => NotBlankIfBlikAliasActionIsRegister::FIELD_REQUIRED_ERROR,
                'message' => 'The BLIK token is required with an alias register action.',
            ]
        ]);
    }

    public function test_paying_using_a_valid_blik_alias(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['shop/blik_payment_method.yml', 'shop/blik_alias.yml', 'shop/common/shop_user.yml']);

        /** @var ShopUserInterface $shopUser */
        $shopUser = $fixtures['shop_user_john_doe'];
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER + $this->generateAuthorizationHeader($shopUser),
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikAliasAction' => BlikAliasAction::APPLY->value,
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponse($response, 'shop/paying_for_orders_by_blik/test_paying_with_a_valid_blik_token_for_an_order');
    }

    public function test_paying_using_an_expired_blik_alias(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['shop/blik_payment_method.yml', 'shop/blik_expired_alias.yml', 'shop/common/shop_user.yml']);

        /** @var ShopUserInterface $shopUser */
        $shopUser = $fixtures['shop_user_john_doe'];
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER + $this->generateAuthorizationHeader($shopUser),
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikAliasAction' => BlikAliasAction::APPLY->value,
            ]),
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame('An error occurred', $content['hydra:title']);
        $this->assertSame('The Blik Alias expired. Please register a new one or use a Blik token.', $content['hydra:description']);
    }

    public function test_paying_using_not_registered_blik_alias(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['shop/blik_payment_method.yml', 'shop/blik_not_registered_alias.yml', 'shop/common/shop_user.yml']);

        /** @var ShopUserInterface $shopUser */
        $shopUser = $fixtures['shop_user_john_doe'];
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER + $this->generateAuthorizationHeader($shopUser),
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikAliasAction' => BlikAliasAction::APPLY->value,
            ]),
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame('An error occurred', $content['hydra:title']);
        $this->assertSame('The Blik Alias is not registered yet. Please try again later or use a Blik token.', $content['hydra:description']);
    }

    public function test_paying_using_a_valid_blik_alias_but_registered_in_more_than_one_bank_app(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['shop/blik_payment_method.yml', 'shop/blik_ambiguous_alias.yml', 'shop/common/shop_user.yml']);

        /** @var ShopUserInterface $shopUser */
        $shopUser = $fixtures['shop_user_john_doe'];
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER + $this->generateAuthorizationHeader($shopUser),
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikAliasAction' => BlikAliasAction::APPLY->value,
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponse($response, 'shop/paying_for_orders_by_blik/test_paying_using_a_valid_blik_alias_but_registered_in_more_than_one_bank_app', Response::HTTP_BAD_REQUEST);
    }

    public function test_paying_using_a_valid_blik_alias_registered_in_different_banks(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['shop/blik_payment_method.yml', 'shop/blik_ambiguous_alias.yml', 'shop/common/shop_user.yml']);

        /** @var ShopUserInterface $shopUser */
        $shopUser = $fixtures['shop_user_john_doe'];
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_blik');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER + $this->generateAuthorizationHeader($shopUser),
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'blikAliasAction' => BlikAliasAction::APPLY->value,
                'blikAliasApplicationCode' => '1ec8fe63-ea6e-6b48-ac6f-f7f170888d37',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponse($response, 'shop/paying_for_orders_by_blik/test_paying_with_a_valid_blik_token_for_an_order');
    }

    public function test_paying_with_a_too_short_blik_token(): void
    {
        $this->loadFixturesFromFile('shop/blik_payment_method.yml');

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
                'code' => Length::NOT_EQUAL_LENGTH_ERROR,
                'message' => 'The BLIK token must have exactly 6 characters.',
            ]
        ]);
    }

    public function test_paying_with_a_too_long_blik_token(): void
    {
        $this->loadFixturesFromFile('shop/blik_payment_method.yml');

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
                'code' => Length::NOT_EQUAL_LENGTH_ERROR,
                'message' => 'The BLIK token must have exactly 6 characters.',
            ]
        ]);
    }

    public function test_paying_without_providing_a_blik_token_or_using_an_alias(): void
    {
        $this->loadFixturesFromFile('shop/blik_payment_method.yml');

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
                'propertyPath' => '',
                'code' => OneOfPropertiesRequiredIfGatewayConfigTypeEquals::ALL_FIELDS_ARE_BLANK_ERROR,
                'message' => 'You must provide a BLIK token or use an alias to pay with BLIK.',
            ]
        ]);
    }
}
