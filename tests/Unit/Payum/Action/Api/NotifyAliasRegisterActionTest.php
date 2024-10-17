<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Entity\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\NotifyAliasRegisterAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify\NotifyData;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\NotifyAliasRegister;
use CommerceWeavers\SyliusTpayPlugin\Resolver\BlikAliasResolverInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class NotifyAliasRegisterActionTest extends TestCase
{
    use ProphecyTrait;

    private BlikAliasResolverInterface|ObjectProphecy $blikAliasResolver;

    private ObjectManager|ObjectProphecy $objectManager;

    protected function setUp(): void
    {
        $this->blikAliasResolver = $this->prophesize(BlikAliasResolverInterface::class);
        $this->objectManager = $this->prophesize(ObjectManager::class);
    }

    public function test_it_saves_blik_alias(): void
    {
        $model = $this->prophesize(PaymentInterface::class);
        $order = $this->prophesize(OrderInterface::class);
        $customer = $this->prophesize(CustomerInterface::class);
        $blikAlias = $this->prophesize(BlikAliasInterface::class);
        $data = new NotifyData(
            'jws',
            <<<JSON
                {
                    "id": "1010",
                    "event": "ALIAS_REGISTER",
                    "msg_value": {
                        "value": "user_unique_alias_123",
                        "type": "UID",
                        "expirationDate": "2024-11-02 14:15:01"
                    },
                    "md5sum": "d303c5af701cdfcaed02f66603239eef"
                }
            JSON,
            []
        );
        $request = $this->prophesize(NotifyAliasRegister::class);
        $request->getModel()->willReturn($model);
        $request->getData()->willReturn($data);
        $model->getOrder()->willReturn($order);
        $order->getCustomer()->willReturn($customer);
        $this->blikAliasResolver->resolve($customer)->willReturn($blikAlias);

        $this->createTestSubject()->execute($request->reveal());

        $blikAlias->setValue('user_unique_alias_123')->shouldBeCalled();
        $blikAlias->setExpirationDate(new \DateTimeImmutable('2024-11-02 14:15:01'))->shouldBeCalled();
        $this->objectManager->persist($blikAlias)->shouldBeCalled();
    }

    public function test_it_throws_exception_if_msg_value_expiration_date_does_not_exist(): void
    {
        $model = $this->prophesize(PaymentInterface::class);
        $data = new NotifyData(
            'jws',
            <<<JSON
                {
                    "id": "1010",
                    "event": "ALIAS_REGISTER",
                    "msg_value": {
                        "value": "user_unique_alias_123",
                        "type": "UID"
                    },
                    "md5sum": "d303c5af701cdfcaed02f66603239eef"
                }
            JSON,
            []
        );
        $request = $this->prophesize(NotifyAliasRegister::class);
        $request->getModel()->willReturn($model);
        $request->getData()->willReturn($data);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The msg_value.expirationDate is missing.');

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_throws_exception_if_msg_value_value_does_not_exist(): void
    {
        $model = $this->prophesize(PaymentInterface::class);
        $data = new NotifyData(
            'jws',
            <<<JSON
                {
                    "id": "1010",
                    "event": "ALIAS_REGISTER",
                    "msg_value": {
                        "type": "UID",
                        "expirationDate": "2024-11-02 14:15:01"
                    },
                    "md5sum": "d303c5af701cdfcaed02f66603239eef"
                }
            JSON,
            []
        );
        $request = $this->prophesize(NotifyAliasRegister::class);
        $request->getModel()->willReturn($model);
        $request->getData()->willReturn($data);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The msg_value.value is missing.');

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_throws_exception_if_msg_value_does_not_exist(): void
    {
        $model = $this->prophesize(PaymentInterface::class);
        $data = new NotifyData(
            'jws',
            <<<JSON
                {
                    "id": "1010",
                    "event": "ALIAS_REGISTER",
                    "md5sum": "d303c5af701cdfcaed02f66603239eef"
                }
            JSON,
            []
        );
        $request = $this->prophesize(NotifyAliasRegister::class);
        $request->getModel()->willReturn($model);
        $request->getData()->willReturn($data);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The msg_value is missing.');

        $this->createTestSubject()->execute($request->reveal());
    }

    private function createTestSubject(): NotifyAliasRegisterAction
    {
        return new NotifyAliasRegisterAction(
            $this->blikAliasResolver->reveal(),
            $this->objectManager->reveal(),
        );
    }
}
