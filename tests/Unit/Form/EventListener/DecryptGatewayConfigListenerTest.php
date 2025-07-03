<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Form\EventListener;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListener;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListenerInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\FormInterface;

final class DecryptGatewayConfigListenerTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_does_nothing_with_any_gateway(): void
    {
        $this->expectNotToPerformAssertions();

        $form = $this->prophesize(FormInterface::class);
        $gateway = $this->prophesize(GatewayInterface::class);

        $this->createTestSubject()->__invoke(new PreSetDataEvent($form->reveal(), $gateway->reveal()));
    }

    private function createTestSubject(): DecryptGatewayConfigListenerInterface
    {
        return new DecryptGatewayConfigListener();
    }
}
