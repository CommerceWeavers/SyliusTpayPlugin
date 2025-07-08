<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Form\EventListener;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListener;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListenerInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\FormInterface;

final class DecryptGatewayConfigListenerTest extends TestCase
{
    use ProphecyTrait;

    private CypherInterface|ObjectProphecy $cypher;

    protected function setUp(): void
    {
        $this->cypher = $this->prophesize(CypherInterface::class);
    }

    public function test_it_does_nothing_when_a_gateway_is_not_crypted(): void
    {
        $this->expectNotToPerformAssertions();

        $form = $this->prophesize(FormInterface::class);
        $gateway = $this->prophesize(GatewayInterface::class);

        $this->createTestSubject()->__invoke(new PreSetDataEvent($form->reveal(), $gateway->reveal()));
    }

    public function test_it_decrypts_a_gateway_config(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $gateway = $this->prophesize(GatewayConfigInterface::class);
        $gateway->willImplement(CryptedInterface::class);

        $gateway->decrypt($this->cypher)->shouldBeCalled();

        $this->createTestSubject()->__invoke(new PreSetDataEvent($form->reveal(), $gateway->reveal()));
    }

    public function test_it_does_nothing_when_cypher_is_null(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $gateway = $this->prophesize(GatewayConfigInterface::class);
        $gateway->willImplement(CryptedInterface::class);

        $gateway->decrypt()->shouldNotBeCalled();

        $this->createTestSubjectWithNullCypher()->__invoke(new PreSetDataEvent($form->reveal(), $gateway->reveal()));
    }

    private function createTestSubject(): DecryptGatewayConfigListenerInterface
    {
        return new DecryptGatewayConfigListener($this->cypher->reveal());
    }

    private function createTestSubjectWithNullCypher(): DecryptGatewayConfigListenerInterface
    {
        return new DecryptGatewayConfigListener(null);
    }
}