<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Twig\Component;

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
        private readonly string $formClass,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->create($this->formClass);
    }
}
