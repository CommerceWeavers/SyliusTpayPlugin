<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin;

final class Routing
{
    public const INIT_APPLE_PAY_PAYMENT = 'commerce_weavers_sylius_tpay_init_apple_pay_payment';

    public const INIT_APPLE_PAY_PAYMENT_PATH = '/tpay/apple-pay/init';

    public const WEBHOOK_PAYMENT_NOTIFICATION = 'commerce_weavers_sylius_tpay_payment_notification';

    public const WEBHOOK_PAYMENT_NOTIFICATION_PATH = '/webhook/{_locale}/tpay/payment-notification';

    public const WEBHOOK_NOTIFICATION = 'commerce_weavers_sylius_tpay_notification';

    public const WEBHOOK_NOTIFICATION_PATH = '/webhook/{_locale}/tpay/notification';

    public const ADMIN_TPAY_CHANNELS = 'commerce_weavers_sylius_tpay_admin_tpay_get_channels';

    public const ADMIN_TPAY_CHANNELS_PATH = '/tpay/channels';

    public const ADMIN_TPAY_REMOVE_PAYMENT_METHOD_IMAGE = 'commerce_weavers_sylius_tpay_admin_remove_payment_method_image';

    public const ADMIN_TPAY_REMOVE_PAYMENT_METHOD_IMAGE_PATH = '/tpay/payment-method/{id}/remove-image';

    public const SHOP_PAYMENT_FAILED = 'commerce_weavers_sylius_tpay_payment_failed';

    public const SHOP_PAYMENT_FAILED_PATH = '/tpay/order/{orderToken}/payment-failed';

    public const SHOP_THANK_YOU = 'commerce_weavers_sylius_tpay_thank_you';

    public const SHOP_THANK_YOU_PATH = '/tpay/order/{orderToken}/thank-you';

    public const SHOP_WAITING_FOR_PAYMENT = 'commerce_weavers_sylius_tpay_waiting_for_payment';

    public const SHOP_WAITING_FOR_PAYMENT_PATH = '/tpay/waiting-for-payment';

    public const SHOP_ACCOUNT_CREDIT_CARD_INDEX = 'commerce_weavers_sylius_tpay_shop_account_credit_card_index';

    public const SHOP_ACCOUNT_CREDIT_CARD_INDEX_PATH = '/account/credit-cards';

    public const SHOP_ACCOUNT_CREDIT_CARD_DELETE = 'commerce_weavers_sylius_tpay_shop_account_credit_card_delete';

    public const SHOP_ACCOUNT_CREDIT_CARD_DELETE_PATH = '/account/credit-cards/{id}/delete';
}
