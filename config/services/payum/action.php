<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\CreateBlik0TransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\CreateCardTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\CreatePayByLinkTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\CreateRedirectBasedTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\GetTpayTransactionsChannelsAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\NotifyAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\PayWithCardAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\CaptureAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\GetStatusAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\RefundAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\TpayGatewayFactory;

return function(ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->public()
    ;

    $services->set(CaptureAction::class)
        ->args([
            service('commerce_weavers.tpay.payum.factory.create_transaction'),
        ])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.capture'])
    ;

    $services->set(CreateCardTransactionAction::class)
        ->args([
            service('commerce_weavers_tpay.tpay.factory.create_card_payment_payload'),
            service('commerce_weavers_tpay.payum.factory.token.notify'),
        ])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.create_card_transaction'])
    ;

    $services->set(CreateBlik0TransactionAction::class)
        ->args([
            service('commerce_weavers_tpay.tpay.factory.create_blik0_payment_payload'),
            service('commerce_weavers_tpay.payum.factory.token.notify'),
        ])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.create_blik0_transaction'])
    ;

    $services->set(CreateRedirectBasedTransactionAction::class)
        ->args([
            service('commerce_weavers_tpay.tpay.factory.create_redirect_based_payment_payload'),
            service('commerce_weavers_tpay.payum.factory.token.notify'),
        ])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.create_redirect_based_transaction'])
    ;

    $services->set(NotifyAction::class)
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.notify'])
    ;

    $services->set(PayWithCardAction::class)
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.pay_with_card'])
    ;

    $services->set(GetStatusAction::class)
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.get_status'])
    ;

    $services->set(RefundAction::class)
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.refund'])
    ;

    $services->set(GetTpayTransactionsChannelsAction::class)
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.get_transactions_channels'])
    ;

    $services->set(CreatePayByLinkTransactionAction::class)
        ->args([
            service('commerce_weavers_tpay.tpay.factory.create_pay_by_link_payment_payload'),
            service('commerce_weavers_tpay.payum.factory.token.notify'),
        ])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.create_pay_by_link_transaction'])
    ;
};
