<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Contract\Tpay;

final class CreateTransactionTest extends TpayTestCase
{
    public function test_it_creates_a_redirect_based_transaction(): void
    {
        $response = $this->tpay->transactions()->createTransaction([
            'amount' => 10.50,
            'description' => 'testowe zamówienie',
            'payer' => [
                'email' => 'don.matteo@nonexisting.cw',
                'name' => 'Don Matteo',
            ],
        ]);

        $this->assertMatchesPattern([
            "result" => "success",
            "requestId" => "@string@",
            "transactionId" => "@string@",
            "title" => "@string@",
            "posId" => "@string@",
            "status" => "pending",
            "date" => [
                "creation" => "@datetime@",
                "realization" => null,
            ],
            "amount" => 10.5,
            "currency" => "PLN",
            "description" => "testowe zamówienie",
            "hiddenDescription" => "",
            "payer" => [
                "payerId" => "@string@",
                "email" => "don.matteo@nonexisting.cw",
                "name" => "Don Matteo",
                "phone" => "",
                "address" => "",
                "city" => "",
                "country" => "PL",
                "postalCode" => "",
            ],
            "payments" => [
                "status" => "pending",
                "method" => null,
                "amountPaid" => 0,
                "date" => [
                    "realization" => null,
                ],
            ],
            "transactionPaymentUrl" => "https://secure.sandbox.tpay.com/?gtitle=@string@",
        ], $response);
    }

    public function test_it_creates_a_success_blik_transaction(): void
    {
        $response = $this->tpay->transactions()->createTransaction([
            'amount' => 10.50,
            'description' => 'testowe zamówienie',
            'payer' => [
                'email' => 'don.matteo@nonexisting.cw',
                'name' => 'Don Matteo',
            ],
            'pay' => [
                'groupId' => 150,
                'blikPaymentData' => [
                    'blikToken' => '777123',
                ],
            ],
        ]);

        $this->assertMatchesPattern([
            "result" => "success",
            "requestId" => "@string@",
            "transactionId" => "@string@",
            "title" => "@string@",
            "posId" => "@string@",
            "status" => "pending",
            "date" => [
                "creation" => "@datetime@",
                "realization" => null,
            ],
            "amount" => 10.5,
            "currency" => "PLN",
            "description" => "testowe zamówienie",
            "hiddenDescription" => "",
            "payer" => [
                "payerId" => "@string@",
                "email" => "don.matteo@nonexisting.cw",
                "name" => "Don Matteo",
                "phone" => "",
                "address" => "",
                "city" => "",
                "country" => "PL",
                "postalCode" => "",
            ],
            "payments" => [
                "status" => "pending",
                "method" => "pay_by_link",
                "amountPaid" => 0,
                "date" => [
                    "realization" => null,
                ],
            ],
        ], $response);
    }

    public function test_it_creates_an_unsuccessful_blik_transaction(): void
    {
        $response = $this->tpay->transactions()->createTransaction([
            'amount' => 10.50,
            'description' => 'testowe zamówienie',
            'payer' => [
                'email' => 'don.matteo@nonexisting.cw',
                'name' => 'Don Matteo',
            ],
            'pay' => [
                'groupId' => 150,
                'blikPaymentData' => [
                    'blikToken' => '111234',
                ],
            ],
        ]);

        $this->assertMatchesPattern([
            "result" => "success",
            "requestId" => "@string@",
            "transactionId" => "@string@",
            "title" => "@string@",
            "posId" => "@string@",
            "status" => "pending",
            "date" => [
                "creation" => "@datetime@",
                "realization" => null,
            ],
            "amount" => 10.5,
            "currency" => "PLN",
            "description" => "testowe zamówienie",
            "hiddenDescription" => "",
            "payer" => [
                "payerId" => "@string@",
                "email" => "don.matteo@nonexisting.cw",
                "name" => "Don Matteo",
                "phone" => "",
                "address" => "",
                "city" => "",
                "country" => "PL",
                "postalCode" => "",
            ],
            "payments" => [
                "status" => "pending",
                "method" => "pay_by_link",
                "amountPaid" => 0,
                "date" => [
                    "realization" => null,
                ],
                "errors" => [
                    [
                        "errorCode" => "payment_failed",
                        "errorMessage" => "An error occurred while making the Blik payment. Please contact technical support.",
                        "fieldName" => null,
                    ],
                ],
            ],
        ], $response);
    }
}
