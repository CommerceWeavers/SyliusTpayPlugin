<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\EventListener;

use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Tpay\OpenApi\Utilities\TpayException;

final class SetTpayDefaultPaymentImageUrlListener
{
    private ?TpayApi $tpayApi = null;

    public function __invoke(PreSubmitEvent $event): void
    {
        $paymentMethod = $event->getData();
        if (!\is_array($paymentMethod) || !isset($paymentMethod['gatewayConfig']['config'])) {
            return;
        }

        $gatewayConfig = $paymentMethod['gatewayConfig']['config'];
        if (!isset($gatewayConfig['client_id'], $gatewayConfig['client_secret']) && null === $this->tpayApi) {
            return;
        }

        if (!isset($gatewayConfig['tpay_channel_id'])) {
            return;
        }

        $paymentTpayChannel = \array_filter(
            $this->getTpayChannelsFromApi($gatewayConfig),
            fn (array $channel) => isset($channel['id']) && $channel['id'] === $gatewayConfig['tpay_channel_id']
        );

        $paymentTpayChannel = \reset($paymentTpayChannel);
        if (false === $paymentTpayChannel) {
            $paymentMethod['defaultImageUrl'] = null;
            $event->setData($paymentMethod);

            return;
        }

        $paymentMethod['defaultImageUrl'] = $paymentTpayChannel['image']['url'];
        $event->setData($paymentMethod);
    }

    private function getTpayChannelsFromApi(array $gatewayConfig): array
    {
        if (null === $this->tpayApi) {
            $this->setTpayApi(new TpayApi(
                $gatewayConfig['client_id'],
                $gatewayConfig['client_secret'],
                isset($gatewayConfig['production_mode']) && ((bool) $gatewayConfig['production_mode']),
            ));
        }

        try {
            $tpayChannels = $this->tpayApi?->transactions()->getChannels();

            return $tpayChannels['channels'] ?? [];
        } catch (TpayException) {
            return [];
        }
    }

    public function setTpayApi(?TpayApi $tpayApi): void
    {
        $this->tpayApi = $tpayApi;
    }
}
