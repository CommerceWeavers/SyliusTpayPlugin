<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\GooglePayPayment\Twig\Hooks;

use CommerceWeavers\SyliusTpayPlugin\GooglePayPayment\Payum\Factory\GatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use Sylius\TwigHooks\Hookable\AbstractHookable;
use Sylius\TwigHooks\Hookable\Metadata\HookableMetadata;
use Sylius\TwigHooks\Hookable\Renderer\HookableRendererInterface;

final readonly class ConfirmOrderButtonHookable implements HookableRendererInterface
{
    public const HOOKABLE_ID = 'sylius_shop.checkout.complete.content.form#navigation';

    public function __construct(
        private HookableRendererInterface $hookableRenderer,
    ) {
    }

    public function render(AbstractHookable $hookable, HookableMetadata $metadata): string
    {
        if ($hookable->id !== self::HOOKABLE_ID) {
            return $this->hookableRenderer->render($hookable, $metadata);
        }

        $order = $metadata->context->offsetGet('resource');
        assert($order instanceof OrderLastNewPaymentAwareInterface);

        $payment = $order->getLastCartPayment();

        $gatewayFactoryName = $payment->getMethod()?->getGatewayConfig()?->getFactoryName();

        return $gatewayFactoryName === GatewayFactory::NAME ? '' : $this->hookableRenderer->render($hookable, $metadata);
    }
}
