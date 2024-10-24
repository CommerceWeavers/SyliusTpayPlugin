<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Factory;

use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\CreateTransactionFactory;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\NotifyFactory;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify\NotifyData;
use PHPUnit\Framework\TestCase;

final class NotifyFactoryTest extends TestCase
{
    public function test_it_creates_notify_object(): void
    {
        $notifyData = new NotifyData('test', 'test', ['test']);

        $result = $this->createTestSubject()->createNewWithModel('test', $notifyData);

        $this->assertNotNull($result);
        $this->assertSame(Notify::class, $result::class);
    }

    private function createTestSubject(): NotifyFactory
    {
        return new NotifyFactory();
    }

}
