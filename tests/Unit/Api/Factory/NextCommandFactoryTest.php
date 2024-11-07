<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Factory;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnresolvableNextCommandException;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnsupportedNextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use PhpParser\Node\Arg;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\Payment;

final class NextCommandFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_supports_returns_true(): void
    {
        $someFactory = $this->prophesize(NextCommandFactoryInterface::class);
        $command = new Pay('token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure');
        $payment = new Payment();

        $this->assertTrue(
            $this->createTestSubject([$someFactory->reveal()])->supports($command, $payment)
        );
    }

    public function test_it_throws_an_exception_when_none_command_factory_is_supported(): void
    {
        $this->expectException(UnresolvableNextCommandException::class);
        $this->expectExceptionMessage('No valid next command found.');

        $command = new Pay('token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure');
        $payment = new Payment();

        $someFactory = $this->prophesize(NextCommandFactoryInterface::class);
        $someFactory->supports($command, $payment)->willReturn(false);

        $anotherFactory = $this->prophesize(NextCommandFactoryInterface::class);
        $anotherFactory->supports($command, $payment)->willReturn(false);

        $nextCommandFactories = [
            $someFactory->reveal(),
            $anotherFactory->reveal(),
        ];

        $this->createTestSubject($nextCommandFactories)->create($command, $payment);
    }

    public function test_it_throws_an_exception_when_more_than_one_command_factory_is_supported(): void
    {
        $this->expectException(UnresolvableNextCommandException::class);
        $this->expectExceptionMessage('Multiple valid next commands found.');

        $command = new Pay('token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure');
        $payment = new Payment();

        $someFactory = $this->prophesize(NextCommandFactoryInterface::class);
        $someFactory->supports($command, $payment)->willReturn(true);
        $someFactory->create($command, $payment)->willReturn(new \stdClass());

        $anotherFactory = $this->prophesize(NextCommandFactoryInterface::class);
        $anotherFactory->supports($command, $payment)->willReturn(true);
        $anotherFactory->create($command, $payment)->willReturn(new \stdClass());

        $nextCommandFactories = [
            $someFactory->reveal(),
            $anotherFactory->reveal(),
        ];

        $this->createTestSubject($nextCommandFactories)->create($command, $payment);
    }

    public function test_it_does_not_return_factory_if_its_unsupported(): void
    {
        $command = new Pay('token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure');
        $payment = new Payment();

        $someFactory = $this->prophesize(NextCommandFactoryInterface::class);
        $someFactory->supports($command, $payment)->willReturn(true);
        $someFactory->create($command, $payment)->willReturn($expectedCommand = new \stdClass());

        $anotherFactory = $this->prophesize(NextCommandFactoryInterface::class);
        $anotherFactory->supports($command, $payment)->willReturn(true);
        $anotherFactory->create($command, $payment)->willThrow(UnsupportedNextCommandFactory::class);

        $nextCommandFactories = [
            $someFactory->reveal(),
            $anotherFactory->reveal(),
        ];

        $actualCommand = $this->createTestSubject($nextCommandFactories)->create($command, $payment);
        $this->assertSame($expectedCommand, $actualCommand);
    }

    public function test_it_returns_a_factored_command(): void
    {
        $command = new Pay('token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure');
        $payment = new Payment();

        $someFactory = $this->prophesize(NextCommandFactoryInterface::class);
        $someFactory->supports($command, $payment)->willReturn(false);
        $someFactory->create($command, $payment)->willReturn(new \stdClass());

        $anotherFactory = $this->prophesize(NextCommandFactoryInterface::class);
        $anotherFactory->supports($command, $payment)->willReturn(true);
        $anotherFactory->create($command, $payment)->willReturn($expectedCommand = new \stdClass());

        $nextCommandFactories = [
            $someFactory->reveal(),
            $anotherFactory->reveal(),
        ];

        $actualCommand = $this->createTestSubject($nextCommandFactories)->create($command, $payment);

        $this->assertSame($expectedCommand, $actualCommand);
    }

    private function createTestSubject(iterable $nextCommandFactories): NextCommandFactoryInterface
    {
        return new NextCommandFactory($nextCommandFactories);
    }
}
