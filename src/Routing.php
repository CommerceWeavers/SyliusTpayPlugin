<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin;

final class Routing
{
    public const INIT_APPLE_PAY_PAYMENT = 'commerce_weavers_sylius_tpay_init_apple_pay_payment';

    public const INIT_APPLE_PAY_PAYMENT_PATH = '/tpay/apple-pay/init';

    public const WEBHOOK_TPAY_NOTIFICATION = 'commerce_weavers_sylius_tpay_notification';

    public const WEBHOOK_TPAY_NOTIFICATION_PATH = '/webhook/{_locale}/tpay/notification';

    public const SHOP_PAYMENT_FAILED = 'commerce_weavers_sylius_tpay_payment_failed';

    public const SHOP_PAYMENT_FAILED_PATH = '/tpay/order/{orderToken}/payment-failed';

    public const SHOP_RETRY_PAYMENT = 'commerce_weavers_sylius_tpay_retry_payment';

    public const SHOP_RETRY_PAYMENT_PATH = '/tpay/order/{orderToken}/retry-payment';

    public const SHOP_THANK_YOU = 'commerce_weavers_sylius_tpay_thank_you';

    public const SHOP_THANK_YOU_PATH = '/tpay/order/{orderToken}/thank-you';

    public const SHOP_WAITING_FOR_PAYMENT = 'commerce_weavers_sylius_tpay_waiting_for_payment';

    public const SHOP_WAITING_FOR_PAYMENT_PATH = '/tpay/waiting-for-payment';
}
