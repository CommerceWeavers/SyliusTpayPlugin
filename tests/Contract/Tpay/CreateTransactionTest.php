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
                "country" => "",
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
            "transactionPaymentUrl" => "https://secure.sandbox.tpay.com/?title=@string@",
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
                "country" => "",
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
                "country" => "",
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

    public function test_it_creates_a_success_google_pay_transaction(): void
    {
        $response = $this->tpay->transactions()->createTransaction([
            'amount' => '14.64',
            'description' => 'testowe zamówienie',
            'payer' => [
                'email' => 'don.matteo@nonexisting.cw',
                'name' => 'Don Matteo',
            ],
            'pay' => [
                'groupId' => 166,
                'googlePayPaymentData' => 'eyJzaWduYXR1cmUiOiJNRVFDSUhnbFFGYWVqWitaZS9mcGljaDV6bDY1b0hyTW0vM1FUU0RRRnMwQzJRZ1ZBaUJYZWZyNXRVWlEwRzNaUk1EeVRDbFVZSUE2MnI3U2VDS0Q2eFVUdXJRL1JBXHUwMDNkXHUwMDNkIiwiaW50ZXJtZWRpYXRlU2lnbmluZ0tleSI6eyJzaWduZWRLZXkiOiJ7XCJrZXlWYWx1ZVwiOlwiTUZrd0V3WUhLb1pJemowQ0FRWUlLb1pJemowREFRY0RRZ0FFTERpU2ZMaUhXZnVJeStSQi80NUtXaFZZaW1CRzlFUVRwV0VTdkR0bnRPc1ZNSzRPTFlIUEh5Y3JFU2NFd0hNNDRDWVRqbEo2MFhna0pnUDFQK2Zpa0FcXHUwMDNkXFx1MDAzZFwiLFwia2V5RXhwaXJhdGlvblwiOlwiMTcyOTEyNDc5NzY1OVwifSIsInNpZ25hdHVyZXMiOlsiTUVVQ0lFaTUvVEVQOFZuWDJ6QU5za0I5RHZITDNKM2lrMVpFR1R5K05UcjllUjdHQWlFQXFpL0NzOGk1Nm84eElLZ3hEZ21jQ1ZKejBYRm1qcThzc0dUR0V1dENTZklcdTAwM2QiXX0sInByb3RvY29sVmVyc2lvbiI6IkVDdjIiLCJzaWduZWRNZXNzYWdlIjoie1wiZW5jcnlwdGVkTWVzc2FnZVwiOlwiQmNFcHlVZEkwQnczS3UwYVkrR0JwbTd1YnNBQ2RlVGFSdE5zMlVvbFd4MlBXMks1amZUbHVvTllqLzFRRExxY21jY1dLdisvY2pKVDBtdGlCMnVlVGMxdTRPek5ScHdONDZFRVJLVHpjWXpIMzBmUm0xaFVBdHB2M3ZtS1FEd3RjRVM2emtsYVREWGZhUjRqVVJydDJoUlRvOEpNU2VWZE1DU2l5QWxXMTVhVk9GU0xCcld3bmNIUFpDSUw4RDNpbk5qWFhqbUcxVmRhQnp2WU5HRncyb2lHTUY0bXhNV3NQZUFJRkJONzdzbm4vTWxZZ1lCaXdlQjFYb1JpY0o2ZFFJaGlId1RjTHpjTStxaGM4L05xZm5IT2gyLy9uNDcwamxwdTg1NFRVMVVZcjlvdDl3REt1QmFGRHREVUtJeFhEZTNhT1U2aEN0UWNBR0grMmRTZVNnSXJJMXFLSDdObm9pR3grNjdjRlFMcGFrbjNJeTJLVjJuUVBCdHl3ckJ2blVFSzMyMEp0WVlFNWxBYTRBVGFobUNXMGxINnY4empPdk5JV3pCK25SSEVrK01qWGtSdjllNVBES3NvZkRhcy9UUmRSM1hNZ1JTWmliampMQ2gxZFBIR1hZVENTNUgrM2dPa3prRkNjdmlIWG4wWHJrdENGdjhnZnBFK2RsMlNNQWdDOVlYdzdFb1FkVmJqS2VnaHRQY3dhb2xhblhycThVZWNHU2lpaVFcXHUwMDNkXFx1MDAzZFwiLFwiZXBoZW1lcmFsUHVibGljS2V5XCI6XCJCSTZVQWNVa2xkWDJpUmE0ZExTajUyZWloZU16VHZ4SjVBSUEydWgzeXdRZWlLOTh1WThLbFdmOGRBSUhGZis0QXBHWjJGZS82dXZJZlorcU1VcDRidkFcXHUwMDNkXCIsXCJ0YWdcIjpcIkhyekhhN1BvOTFsTHdGRVpyREhzNWc4NGpEdHdPNTFFazBOa2J6U2JoV1lcXHUwMDNkXCJ9In0=',
            ],
        ]);

        $this->assertMatchesPattern([
            'result' => 'success',
            'requestId' => '@string@',
            'transactionId' => '@string@',
            'title' => '@string@',
            'posId' => '@string@',
            'status' => 'pending',
            'date' => [
                'creation' => '@datetime@',
                'realization' => null,
            ],
            'amount' => 14.64,
            'currency' => 'PLN',
            'description' => 'testowe zamówienie',
            'hiddenDescription' => '',
            'payer' => [
                'payerId' => '@string@',
                'email' => 'don.matteo@nonexisting.cw',
                'name' => 'Don Matteo',
                'phone' => '',
                'address' => '',
                'city' => '',
                'country' => '',
                'postalCode' => '',
            ],
            'payments' => [
                'status' => 'pending',
                'method' => 'pay_by_link',
                'amountPaid' => 0,
                'date' => [
                    'realization' => null,
                ],
            ],
            'transactionPaymentUrl' => "@string@.matchRegex('/^https:\/\//')"
        ], $response);
    }

    public function test_it_creates_an_unsuccessful_google_pay_transaction(): void
    {
        $response = $this->tpay->transactions()->createTransaction([
            'amount' => '14.64',
            'description' => 'testowe zamówienie',
            'payer' => [
                'email' => 'don.matteo@nonexisting.cw',
                'name' => 'Don Matteo',
            ],
            'pay' => [
                'groupId' => 166,
                'googlePayPaymentData' => 'eyJzaWduYXR1cmUiOiJpbnZhbGlkTUVRQ0lCdXBpQWVCcEI4TUhnU25MSEp5TVpQOWhtbGtUeHJWMmtscm40Ris3aDlvQWlBaldEOWRnNFdYRU81RWFmdTRMOHYvZXRneGR1dnVYRTZtdGxkQzcxYms2Z1x1MDAzZFx1MDAzZCIsImludGVybWVkaWF0ZVNpZ25pbmdLZXkiOnsic2lnbmVkS2V5Ijoie1wia2V5VmFsdWVcIjpcIk1Ga3dFd1lIS29aSXpqMENBUVlJS29aSXpqMERBUWNEUWdBRUZPSjVPZkhFNXZrR002SUd3ZytwT2lzVFByRGtaUG0ycUlzd05WZE1idVlJNFluc0dmZlNobFVlVmlNUDJ1VWZjekJiaGNNdEZzOUNoblJiSk54YmJRXHUwMDNkXHUwMDNkXCIsXCJrZXlFeHBpcmF0aW9uXCI6XCIxNzI5MTE1NzM0MDg0XCJ9Iiwic2lnbmF0dXJlcyI6WyJNRVVDSUViVm9WZ055VzJvTVAyQlo2aGcwWkNsL3lkZlhzTVo2WmJoTnhEamEyUzJBaUVBd09Bb1crbnRzMXhpNklabHlXbUhrdTF1UXIwL3V6bC9GQnhXeS9DVTR2QVx1MDAzZCJdfSwicHJvdG9jb2xWZXJzaW9uIjoiRUN2MiIsInNpZ25lZE1lc3NhZ2UiOiJ7XCJlbmNyeXB0ZWRNZXNzYWdlXCI6XCJrK0JLeVVxdnBhbmd2M0N4M3VpMVdRdmNFYURHSkgwMzhFaTRrRHZSa215RjZTZUZ1ODNJNlh0WE95UEZUZ0x3d3RWa2NyRzRMaXdILzEveVQreklSRlRETUpDYVU1ZTMvRmdCcHVNdEVjVjZOOVdGK1pNRWcxNDc2MnBOS213dG9RbUo1WXpRT3RDeHFBdThkOGZ3VnBxVy8yMHh4WUExR3ovRTBicEwwVFVVbjRyVHkyQkNrQmZad2ZkMFlHaWd0T3RvWUNhNWszUk1NZVhZK2F5Z0xUZTFka2JmSEEwL2pJbXg2Ry9zMGhHcUVWZXVPYTk4ZVg2SmJhcllWTXdkRThTeFJvRUFJM21ISjhBaGNyRjc1WkpWVjRwc2huM3VTcnYvcDhKbHgvenoyZ09acVZHTmJlcldsTkVQeUFieVAyT3Rpc1BMTGFHWWF5WFNjd3RPQVJwQjRuNENKM0ZqMGhpZkRRUms5eXY0VFVTR3F5eWw4Qms3WHphZlJmK1M1dE9hNjNIenVMY3huWXd0UEZOSjYvTktRdTFoSkF4dnA2cEx0cjVxS2VOT2pTZStQclRuU1VwMTJ2QlpqSy9rZjlxSThwbTBFenU3dERMZzRjOGtCQWxrd2RZYWg5d0F6dXRjbHZhZW1zRFVkZDNTZnIrSEMvdFhMWmlGWm9oM0V0dEZQMmRWbURmWWFYbTdFWmhQQmRvWWZFMmdaUTl5RXAvK2w2TERsZ1x1MDAzZFx1MDAzZFwiLFwiZXBoZW1lcmFsUHVibGljS2V5XCI6XCJCQTVpdWx1dVc5djZaSS9wRXJTWHIrcTFndXF3aVdMK3R4akRhc09QY1B6Y21kSjY4MEcrOURYeUxJSlVoL3VhVkRwNldBN2ZkanhKRDJsSlo0eWs4NFVcdTAwM2RcIixcInRhZ1wiOlwiT3VOWTNCRVlhajdCaUVxSDZHSDJ0MmNVcXFmSXF5ZnRpaFlrdW01MmQxZ1x1MDAzZFwifSJ9',
            ],
        ]);

        $this->assertMatchesPattern([
            'result' => 'failed',
            'requestId' => '@string@',
            'errors' => [
                [
                    'errorCode' => 'payment_failed',
                    'errorMessage' => 'Payment failed',
                    'fieldName' => '',
                    'devMessage' => 'Please try again later',
                    'docUrl' => 'https://openapi.tpay.com/#/transactions/post_transactions',
                ],
            ],
        ], $response);
    }
}
