<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Form\EventListener;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\EncryptGatewayConfigListener;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\EncryptGatewayConfigListenerInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormInterface;

final class EncryptGatewayConfigListenerTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_does_nothing_with_any_gateway(): void
    {
        $this->expectNotToPerformAssertions();

        $form = $this->prophesize(FormInterface::class);
        $gateway = $this->prophesize(GatewayInterface::class);

        $this->createTestSubject()->__invoke(new PostSubmitEvent($form->reveal(), $gateway->reveal()));
    }

    private function createTestSubject(): EncryptGatewayConfigListenerInterface
    {
        return new EncryptGatewayConfigListener();
    }
}
