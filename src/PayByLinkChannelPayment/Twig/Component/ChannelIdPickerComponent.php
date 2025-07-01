<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Twig\Component;

use Sylius\TwigHooks\LiveComponent\HookableLiveComponentTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ChannelIdPickerComponent
{
    use DefaultActionTrait;
    use HookableLiveComponentTrait;
}
