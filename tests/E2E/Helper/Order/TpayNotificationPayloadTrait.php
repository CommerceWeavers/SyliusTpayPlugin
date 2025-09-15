<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order;

trait TpayNotificationPayloadTrait
{
    protected function getCreditCardNotificationPayload(): array
    {
        return [
            'tr_status' => 'TRUE',
            'id' => '12345',
            'tr_id' => 'TR-TEST-123',
            'tr_amount' => '10.00',
            'tr_crc' => 'crc',
            'md5sum' => md5('dummy'),
            'card_token' => 'tok_abcdef',
            'card_brand' => 'VISA',
            'card_tail' => '1111',
            'token_expiry_date' => '0129',
        ];
    }
}
