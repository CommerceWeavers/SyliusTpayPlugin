<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Factory;

use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\CreateTransactionFactory;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use PHPUnit\Framework\TestCase;

final class CreateTransactionFactoryTest extends TestCase
{
    public function test_it_creates_create_transaction(): void
    {
        $result = $this->createTestSubject()->createNewWithModel('test');

        $this->assertNotNull($result);
        $this->assertSame(CreateTransaction::class, $result::class);
    }

    private function createTestSubject(): CreateTransactionFactory
    {
        return new CreateTransactionFactory();
    }

}
