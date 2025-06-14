<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Controller\DisplayPaymentFailedPageAction;
use CommerceWeavers\SyliusTpayPlugin\Controller\DisplayThankYouPageAction;
use CommerceWeavers\SyliusTpayPlugin\Controller\DisplayWaitingForPaymentPage;
use CommerceWeavers\SyliusTpayPlugin\Controller\RetryPaymentAction;
use CommerceWeavers\SyliusTpayPlugin\Routing;
use Symfony\Component\HttpFoundation\Request;

return function(RoutingConfigurator $routes): void {
    $routes->add(Routing::SHOP_PAYMENT_FAILED, Routing::SHOP_PAYMENT_FAILED_PATH)
        ->controller(DisplayPaymentFailedPageAction::class)
        ->methods([Request::METHOD_GET])
    ;

    $routes->add(Routing::SHOP_RETRY_PAYMENT, Routing::SHOP_RETRY_PAYMENT_PATH)
        ->controller(RetryPaymentAction::class)
        ->methods([Request::METHOD_POST])
    ;

    $routes->add(Routing::SHOP_THANK_YOU, Routing::SHOP_THANK_YOU_PATH)
        ->controller(DisplayThankYouPageAction::class)
        ->methods([Request::METHOD_GET])
    ;

    $routes->add(Routing::SHOP_WAITING_FOR_PAYMENT, Routing::SHOP_WAITING_FOR_PAYMENT_PATH)
        ->controller(DisplayWaitingForPaymentPage::class)
        ->methods([Request::METHOD_GET])
    ;

    $routes->add(Routing::SHOP_ACCOUNT_CREDIT_CARD_INDEX, Routing::SHOP_ACCOUNT_CREDIT_CARD_INDEX_PATH)
        ->controller('commerce_weavers_sylius_tpay.controller.credit_card::indexAction')
        ->methods([Request::METHOD_GET])
        ->defaults([
            '_sylius' => [
                'template' => '@CommerceWeaversSyliusTpayPlugin/shop/account/credit_card/index.html.twig',
                'section' => 'shop_account',
                'grid' => 'commerce_weavers_sylius_tpay_shop_account_credit_card',
            ]
        ])
    ;

    $routes->add(Routing::SHOP_ACCOUNT_CREDIT_CARD_DELETE, Routing::SHOP_ACCOUNT_CREDIT_CARD_DELETE_PATH)
        ->controller('commerce_weavers_sylius_tpay.controller.credit_card::deleteAction')
        ->methods([Request::METHOD_DELETE])
        ->requirements(['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[1-6][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}'])
        ->defaults([
            '_sylius' => [
                'section' => 'shop_account',
                'repository' => [
                    'method' => 'findOneByIdCustomerAndChannel',
                    'arguments' => [
                        '$id',
                        'expr:service(\'sylius.context.customer\').getCustomer()',
                        'expr:service(\'sylius.context.channel\').getChannel()',
                    ],
                ],
                'redirect' => Routing::SHOP_ACCOUNT_CREDIT_CARD_INDEX,
            ]
        ])
    ;
};
