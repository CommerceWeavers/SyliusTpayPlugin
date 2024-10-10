<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin;

final class Routing
{
    public const WEBHOOK_PAYMENT_NOTIFICATION = 'commerce_weavers_sylius_tpay_payment_notification';

    public const WEBHOOK_PAYMENT_NOTIFICATION_PATH = '/webhook/{_locale}/tpay/payment-notification';

    public const SHOP_WAITING_FOR_PAYMENT = 'commerce_weavers_sylius_tpay_waiting_for_payment';

    public const SHOP_WAITING_FOR_PAYMENT_PATH = '/tpay/waiting-for-payment';
}
