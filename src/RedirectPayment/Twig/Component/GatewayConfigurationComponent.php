<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\RedirectPayment\Twig\Component;

use CommerceWeavers\SyliusTpayPlugin\RedirectPayment\Form\Type\GatewayConfigurationType;
use Sylius\TwigHooks\Twig\Component\HookableComponentTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class GatewayConfigurationComponent
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;
    use HookableComponentTrait;

    public function __construct(
        private FormFactoryInterface $formFactory,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->create(GatewayConfigurationType::class);
    }
}
