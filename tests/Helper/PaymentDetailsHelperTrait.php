<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Helper;

trait PaymentDetailsHelperTrait
{
    protected function getExpectedDetails(...$overriddenDetails): array
    {
        $expectedDetails = [
            'tpay' => [
                'transaction_id' => null,
                'result' => null,
                'status' => null,
                'apple_pay_token' => null,
                'blik_token' => null,
                'google_pay_token' => null,
                'card' => null,
                'apple_pay_session' => null,
                'payment_url' => null,
                'success_url' => null,
                'failure_url' => null,
                'tpay_channel_id' => null,
                'visa_mobile_phone_number' => null,
            ],
        ];

        foreach ($overriddenDetails as $key => $value) {
            $expectedDetails['tpay'][$key] = $value;
        }

        return $expectedDetails;
    }
}