<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayBySavedCard;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayBySavedCardHandler;
use CommerceWeavers\SyliusTpayPlugin\Payum\Processor\CreateTransactionProcessorInterface;
use Payum\Core\Payum;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\CommerceWeavers\SyliusTpayPlugin\Helper\PaymentDetailsHelperTrait;

final class PayBySavedCardHandlerTest extends TestCase
{
    use ProphecyTrait;

    use PaymentDetailsHelperTrait;

    private PaymentRepositoryInterface|ObjectProphecy $paymentRepository;

    private Payum|ObjectProphecy $payum;

    private CreateTransactionProcessorInterface|ObjectProphecy $createTransactionProcessor;

    protected function setUp(): void
    {
        $this->paymentRepository = $this->prophesize(PaymentRepositoryInterface::class);
        $this->createTransactionProcessor = $this->prophesize(CreateTransactionProcessorInterface::class);
    }

    public function test_it_throw_an_exception_if_a_payment_cannot_be_found(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Payment with id "1" cannot be found.');

        $this->paymentRepository->find(1)->willReturn(null);

        $this->createTestSubject()->__invoke(new PayBySavedCard(1, 'e0f79275-18ef-4edf-b8fc-adc40fdcbcc0'));
    }

    public function test_it_creates_a_card_based_transaction(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([], ['tpay' => ['status' => 'success']]);
        $payment->setDetails(
            $this->getExpectedDetails(use_saved_credit_card: 'e0f79275-18ef-4edf-b8fc-adc40fdcbcc0'),
        )->shouldBeCalled();

        $this->paymentRepository->find(1)->willReturn($payment);

        $result = $this->createTestSubject()->__invoke(new PayBySavedCard(1, 'e0f79275-18ef-4edf-b8fc-adc40fdcbcc0'));

        $this->assertSame('success', $result->status);
        $this->assertNull($result->transactionPaymentUrl);
    }

    public function test_it_creates_a_card_based_transaction_with_payment_url(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([], ['tpay' => ['status' => 'pending', 'payment_url' => 'https://cw.org/pay']]);
        $payment->setDetails(
            $this->getExpectedDetails(use_saved_credit_card: 'e0f79275-18ef-4edf-b8fc-adc40fdcbcc0'),
        )->shouldBeCalled();

        $this->paymentRepository->find(1)->willReturn($payment);

        $result = $this->createTestSubject()->__invoke(new PayBySavedCard(1, 'e0f79275-18ef-4edf-b8fc-adc40fdcbcc0'));

        $this->assertSame('pending', $result->status);
        $this->assertSame('https://cw.org/pay', $result->transactionPaymentUrl);
    }

    private function createTestSubject(): PayBySavedCardHandler
    {
        return new PayBySavedCardHandler(
            $this->paymentRepository->reveal(),
            $this->createTransactionProcessor->reveal(),
        );
    }
}
