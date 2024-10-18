<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Entity\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\NotifyAliasUnregisterAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify\NotifyData;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\NotifyAliasRegister;
use CommerceWeavers\SyliusTpayPlugin\Repository\BlikAliasRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use Payum\Core\Reply\HttpResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class NotifyAliasUnregisterActionTest extends TestCase
{
    use ProphecyTrait;

    private BlikAliasRepositoryInterface|ObjectProphecy $blikAliasRepository;

    private ObjectManager|ObjectProphecy $blikAliasManager;

    protected function setUp(): void
    {
        $this->blikAliasRepository = $this->prophesize(BlikAliasRepositoryInterface::class);
        $this->blikAliasManager = $this->prophesize(ObjectManager::class);
    }

    public function test_it_removes_unregistered_blik_alias(): void
    {
        $blikAlias = $this->prophesize(BlikAliasInterface::class);
        $data = new NotifyData(
            'jws',
            <<<JSON
                {
                    "id": "1010",
                    "event": "ALIAS_UNREGISTER",
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
        $request->getData()->willReturn($data);
        $this->blikAliasRepository->findOneByValue('user_unique_alias_123')->willReturn($blikAlias);

        $this->expectExceptionObject(new HttpResponse('TRUE', 200));

        $this->createTestSubject()->execute($request->reveal());

        $this->blikAliasManager->remove($blikAlias)->shouldBeCalled();
    }

    public function test_it_removes_expired_blik_alias(): void
    {
        $blikAlias = $this->prophesize(BlikAliasInterface::class);
        $data = new NotifyData(
            'jws',
            <<<JSON
                {
                    "id": "1010",
                    "event": "ALIAS_EXPIRED",
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
        $request->getData()->willReturn($data);
        $this->blikAliasRepository->findOneByValue('user_unique_alias_123')->willReturn($blikAlias);

        $this->expectExceptionObject(new HttpResponse('TRUE', 200));

        $this->createTestSubject()->execute($request->reveal());

        $this->blikAliasManager->remove($blikAlias)->shouldBeCalled();
    }

    public function test_it_throws_exception_if_msg_value_value_does_not_exist(): void
    {
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
        $request->getData()->willReturn($data);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The msg_value.value is missing.');

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_throws_exception_if_blik_alias_not_found(): void
    {
        $data = new NotifyData(
            'jws',
            <<<JSON
                {
                    "id": "1010",
                    "event": "ALIAS_EXPIRED",
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
        $request->getData()->willReturn($data);
        $this->blikAliasRepository->findOneByValue('user_unique_alias_123')->willReturn(null);

        $this->expectExceptionObject(new HttpResponse('FALSE - Alias not found', 400));

        $this->createTestSubject()->execute($request->reveal());
    }

    private function createTestSubject(): NotifyAliasUnregisterAction
    {
        return new NotifyAliasUnregisterAction(
            $this->blikAliasRepository->reveal(),
            $this->blikAliasManager->reveal(),
        );
    }
}
