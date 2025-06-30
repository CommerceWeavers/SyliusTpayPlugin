<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Test\Workflow;

final class SimpleStateMachineFactory
{
    public function get($subject, $name = null): SimpleStateMachine
    {
        return new SimpleStateMachine();
    }
}

final class SimpleStateMachine
{
    public function can($transition): bool
    {
        return true;
    }

    public function apply($transition): void
    {
        // Do nothing
    }

    public function getEnabledTransitions(): array
    {
        return [];
    }

    public function getMarkingType(): ?string
    {
        return null;
    }
}