<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Api\Shop;

use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\EncodedGooglePayToken;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayConfigTypeEquals;
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

        $this->loadFixturesFromFile('shop/paying_for_orders_by_google_pay.yml');
    }

    public function test_paying_with_a_valid_google_pay_token_for_an_order(): void
    {
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_google_pay');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'googlePayToken' => 'eyJzaWduYXR1cmUiOiJNRVFDSUhnbFFGYWVqWitaZS9mcGljaDV6bDY1b0hyTW0vM1FUU0RRRnMwQzJRZ1ZBaUJYZWZyNXRVWlEwRzNaUk1EeVRDbFVZSUE2MnI3U2VDS0Q2eFVUdXJRL1JBXHUwMDNkXHUwMDNkIiwiaW50ZXJtZWRpYXRlU2lnbmluZ0tleSI6eyJzaWduZWRLZXkiOiJ7XCJrZXlWYWx1ZVwiOlwiTUZrd0V3WUhLb1pJemowQ0FRWUlLb1pJemowREFRY0RRZ0FFTERpU2ZMaUhXZnVJeStSQi80NUtXaFZZaW1CRzlFUVRwV0VTdkR0bnRPc1ZNSzRPTFlIUEh5Y3JFU2NFd0hNNDRDWVRqbEo2MFhna0pnUDFQK2Zpa0FcXHUwMDNkXFx1MDAzZFwiLFwia2V5RXhwaXJhdGlvblwiOlwiMTcyOTEyNDc5NzY1OVwifSIsInNpZ25hdHVyZXMiOlsiTUVVQ0lFaTUvVEVQOFZuWDJ6QU5za0I5RHZITDNKM2lrMVpFR1R5K05UcjllUjdHQWlFQXFpL0NzOGk1Nm84eElLZ3hEZ21jQ1ZKejBYRm1qcThzc0dUR0V1dENTZklcdTAwM2QiXX0sInByb3RvY29sVmVyc2lvbiI6IkVDdjIiLCJzaWduZWRNZXNzYWdlIjoie1wiZW5jcnlwdGVkTWVzc2FnZVwiOlwiQmNFcHlVZEkwQnczS3UwYVkrR0JwbTd1YnNBQ2RlVGFSdE5zMlVvbFd4MlBXMks1amZUbHVvTllqLzFRRExxY21jY1dLdisvY2pKVDBtdGlCMnVlVGMxdTRPek5ScHdONDZFRVJLVHpjWXpIMzBmUm0xaFVBdHB2M3ZtS1FEd3RjRVM2emtsYVREWGZhUjRqVVJydDJoUlRvOEpNU2VWZE1DU2l5QWxXMTVhVk9GU0xCcld3bmNIUFpDSUw4RDNpbk5qWFhqbUcxVmRhQnp2WU5HRncyb2lHTUY0bXhNV3NQZUFJRkJONzdzbm4vTWxZZ1lCaXdlQjFYb1JpY0o2ZFFJaGlId1RjTHpjTStxaGM4L05xZm5IT2gyLy9uNDcwamxwdTg1NFRVMVVZcjlvdDl3REt1QmFGRHREVUtJeFhEZTNhT1U2aEN0UWNBR0grMmRTZVNnSXJJMXFLSDdObm9pR3grNjdjRlFMcGFrbjNJeTJLVjJuUVBCdHl3ckJ2blVFSzMyMEp0WVlFNWxBYTRBVGFobUNXMGxINnY4empPdk5JV3pCK25SSEVrK01qWGtSdjllNVBES3NvZkRhcy9UUmRSM1hNZ1JTWmliampMQ2gxZFBIR1hZVENTNUgrM2dPa3prRkNjdmlIWG4wWHJrdENGdjhnZnBFK2RsMlNNQWdDOVlYdzdFb1FkVmJqS2VnaHRQY3dhb2xhblhycThVZWNHU2lpaVFcXHUwMDNkXFx1MDAzZFwiLFwiZXBoZW1lcmFsUHVibGljS2V5XCI6XCJCSTZVQWNVa2xkWDJpUmE0ZExTajUyZWloZU16VHZ4SjVBSUEydWgzeXdRZWlLOTh1WThLbFdmOGRBSUhGZis0QXBHWjJGZS82dXZJZlorcU1VcDRidkFcXHUwMDNkXCIsXCJ0YWdcIjpcIkhyekhhN1BvOTFsTHdGRVpyREhzNWc4NGpEdHdPNTFFazBOa2J6U2JoV1lcXHUwMDNkXCJ9In0=',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);
        $this->assertResponse($response, 'shop/paying_for_orders_by_google_pay/test_paying_with_a_valid_token_for_an_order');
    }

    public function test_paying_with_not_encoded_google_pay_token(): void
    {
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
                'code' => EncodedGooglePayToken::NOT_ENCODED_ERROR,
                'message' => 'The Google Pay token must be a JSON object encoded with Base64.',
            ]
        ]);
    }

    public function test_paying_with_a_google_pay_token_that_is_not_a_json_object(): void
    {
        $order = $this->doPlaceOrder('t0k3n', paymentMethodCode: 'tpay_google_pay');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/v2/shop/orders/%s/pay', $order->getTokenValue()),
            server: self::CONTENT_TYPE_HEADER,
            content: json_encode([
                'successUrl' => 'https://example.com/success',
                'failureUrl' => 'https://example.com/failure',
                'googlePayToken' => 'c29tZUludmFsaWRWYWx1ZQ==',
            ]),
        );

        $response = $this->client->getResponse();

        $this->assertResponseViolations($response, [
            [
                'propertyPath' => 'googlePayToken',
                'code' => EncodedGooglePayToken::NOT_JSON_ENCODED_ERROR,
                'message' => 'The Google Pay token must be a JSON object encoded with Base64.',
            ]
        ]);
    }

    public function test_paying_without_providing_a_google_pay_token(): void
    {
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
                'code' => NotBlankIfGatewayConfigTypeEquals::FIELD_REQUIRED_ERROR,
                'message' => 'The Google Pay token is required.',
            ]
        ]);
    }
}
