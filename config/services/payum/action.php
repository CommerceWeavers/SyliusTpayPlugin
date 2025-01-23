<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\ApplePayPayment\Payum\Factory\GatewayFactory as ApplePayGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Payum\Factory\GatewayFactory as BlikGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Payum\Factory\GatewayFactory as CardGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\GooglePayPayment\Payum\Factory\GatewayFactory as GooglePayGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Factory\GatewayFactory as PayByLinkGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Payum\Factory\GatewayFactory as PayByLinkChannelGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\NotifyAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\CaptureAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\GetStatusAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\PartialRefundAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\RefundAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\ResolveNextRouteAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\TpayGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\RedirectPayment\Payum\Factory\GatewayFactory as RedirectGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\VisaMobilePayment\Payum\Factory\GatewayFactory as VisaMobileGatewatFactory;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->public()
        ->tag('payum.action', ['factory' => VisaMobileGatewatFactory::NAME])
    ;

    $services->set(CaptureAction::class)
        ->args([
            service('commerce_weavers_sylius_tpay.payum.factory.create_transaction'),
        ])
        ->tag('payum.action', ['factory' => ApplePayGatewayFactory::NAME, 'alias' => 'cw.tpay_apple_pay.capture'])
        ->tag('payum.action', ['factory' => BlikGatewayFactory::NAME, 'alias' => 'cw.tpay_blik.capture'])
        ->tag('payum.action', ['factory' => CardGatewayFactory::NAME, 'alias' => 'cw.tpay_card.capture'])
        ->tag('payum.action', ['factory' => GooglePayGatewayFactory::NAME, 'alias' => 'cw.tpay_google_pay.capture'])
        ->tag('payum.action', ['factory' => PayByLinkGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl.capture'])
        ->tag('payum.action', ['factory' => PayByLinkChannelGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl_channel.capture'])
        ->tag('payum.action', ['factory' => RedirectGatewayFactory::NAME, 'alias' => 'cw.tpay_redirect.capture'])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.capture'])
    ;

    $services->set(NotifyAction::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.security.notification.factory.basic_payment'),
            service('commerce_weavers_sylius_tpay.tpay.security.notification.verifier.checksum'),
            service('commerce_weavers_sylius_tpay.tpay.security.notification.verifier.signature'),
        ])
        ->tag('payum.action', ['factory' => ApplePayGatewayFactory::NAME, 'alias' => 'cw.tpay_apple_pay.notify'])
        ->tag('payum.action', ['factory' => BlikGatewayFactory::NAME, 'alias' => 'cw.tpay_blik.notify'])
        ->tag('payum.action', ['factory' => CardGatewayFactory::NAME, 'alias' => 'cw.tpay_card.notify'])
        ->tag('payum.action', ['factory' => GooglePayGatewayFactory::NAME, 'alias' => 'cw.tpay_google_pay.notify'])
        ->tag('payum.action', ['factory' => PayByLinkGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl.notify'])
        ->tag('payum.action', ['factory' => PayByLinkChannelGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl_channel.notify'])
        ->tag('payum.action', ['factory' => RedirectGatewayFactory::NAME, 'alias' => 'cw.tpay_redirect.notify'])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.notify'])
    ;

    $services->set(GetStatusAction::class)
        ->tag('payum.action', ['factory' => ApplePayGatewayFactory::NAME, 'alias' => 'cw.tpay_apple_pay.get_status'])
        ->tag('payum.action', ['factory' => BlikGatewayFactory::NAME, 'alias' => 'cw.tpay_blik.get_status'])
        ->tag('payum.action', ['factory' => CardGatewayFactory::NAME, 'alias' => 'cw.tpay_card.get_status'])
        ->tag('payum.action', ['factory' => GooglePayGatewayFactory::NAME, 'alias' => 'cw.tpay_google_pay.get_status'])
        ->tag('payum.action', ['factory' => PayByLinkGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl.get_status'])
        ->tag('payum.action', ['factory' => PayByLinkChannelGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl_channel.get_status'])
        ->tag('payum.action', ['factory' => RedirectGatewayFactory::NAME, 'alias' => 'cw.tpay_redirect.get_status'])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.get_status'])
    ;

    $services->set(PartialRefundAction::class)
        ->tag('payum.action', ['factory' => ApplePayGatewayFactory::NAME, 'alias' => 'cw.tpay_apple_pay.partial_refund'])
        ->tag('payum.action', ['factory' => BlikGatewayFactory::NAME, 'alias' => 'cw.tpay_blik.partial_refund'])
        ->tag('payum.action', ['factory' => CardGatewayFactory::NAME, 'alias' => 'cw.tpay_card.partial_refund'])
        ->tag('payum.action', ['factory' => GooglePayGatewayFactory::NAME, 'alias' => 'cw.tpay_google_pay.partial_refund'])
        ->tag('payum.action', ['factory' => PayByLinkGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl.partial_refund'])
        ->tag('payum.action', ['factory' => PayByLinkChannelGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl_channel.partial_refund'])
        ->tag('payum.action', ['factory' => RedirectGatewayFactory::NAME, 'alias' => 'cw.tpay_redirect.partial_refund'])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.partial_refund'])
    ;

    $services->set(RefundAction::class)
        ->tag('payum.action', ['factory' => ApplePayGatewayFactory::NAME, 'alias' => 'cw.tpay_apple_pay.refund'])
        ->tag('payum.action', ['factory' => BlikGatewayFactory::NAME, 'alias' => 'cw.tpay_blik.refund'])
        ->tag('payum.action', ['factory' => CardGatewayFactory::NAME, 'alias' => 'cw.tpay_card.refund'])
        ->tag('payum.action', ['factory' => GooglePayGatewayFactory::NAME, 'alias' => 'cw.tpay_google_pay.refund'])
        ->tag('payum.action', ['factory' => PayByLinkGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl.refund'])
        ->tag('payum.action', ['factory' => PayByLinkChannelGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl_channel.refund'])
        ->tag('payum.action', ['factory' => RedirectGatewayFactory::NAME, 'alias' => 'cw.tpay_redirect.refund'])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.refund'])
    ;

    $services->set(ResolveNextRouteAction::class)
        ->tag('payum.action', ['factory' => ApplePayGatewayFactory::NAME, 'alias' => 'cw.tpay_apple_pay.resolve_next_route'])
        ->tag('payum.action', ['factory' => BlikGatewayFactory::NAME, 'alias' => 'cw.tpay_blik.resolve_next_route'])
        ->tag('payum.action', ['factory' => CardGatewayFactory::NAME, 'alias' => 'cw.tpay_card.resolve_next_route'])
        ->tag('payum.action', ['factory' => GooglePayGatewayFactory::NAME, 'alias' => 'cw.tpay_google_pay.resolve_next_route'])
        ->tag('payum.action', ['factory' => PayByLinkGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl.resolve_next_route'])
        ->tag('payum.action', ['factory' => PayByLinkChannelGatewayFactory::NAME, 'alias' => 'cw.tpay_pbl_channel.resolve_next_route'])
        ->tag('payum.action', ['factory' => RedirectGatewayFactory::NAME, 'alias' => 'cw.tpay_redirect.resolve_next_route'])
        ->tag('payum.action', ['factory' => TpayGatewayFactory::NAME, 'alias' => 'cw.tpay.resolve_next_route'])
    ;
};
