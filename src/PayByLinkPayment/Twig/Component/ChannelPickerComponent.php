<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Twig\Component;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\OrderAwareValidTpayChannelListProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Resource\Model\ResourceInterface;
use Sylius\TwigHooks\Twig\Component\HookableComponentTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class ChannelPickerComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;
    use HookableComponentTrait;

    #[LiveProp(hydrateWith: 'hydrateOrder', dehydrateWith: 'dehydrateOrder')]
    public OrderInterface $order;

    #[LiveProp]
    #[ExposeInTemplate(name: 'selected_channel_id')]
    public ?int $selectedChannelId = null;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderAwareValidTpayChannelListProviderInterface $channelListProvider,
    ) {
    }

    public function hydrateOrder(mixed $value): ?ResourceInterface
    {
        return $this->orderRepository->find($value);
    }

    public function dehydrateOrder(OrderInterface $order): int
    {
        return $order->getId();
    }

    #[ExposeInTemplate(name: 'payment_channels')]
    public function getPaymentChannels(): iterable
    {
        return $this->channelListProvider->provide($this->order);
    }

    #[LiveAction]
    public function pickChannel(#[LiveArg] int $channelId): void
    {
        $this->selectedChannelId = $channelId;
        $this->dispatchBrowserEvent('cw_tpay:pay_by_link:channel_selected', ['channelId' => $channelId]);
    }
}
