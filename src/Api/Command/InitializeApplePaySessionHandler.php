<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Exception\OrderCannotBeFoundException;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\InitializeApplePayPayment;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final class InitializeApplePaySessionHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly GatewayInterface $gateway,
    ) {
    }

    public function __invoke(InitializeApplePaySession $command): InitializeApplePaySessionResult
    {
        $this->verifyOrderExist($command->orderToken);

        $this->gateway->execute(
            new InitializeApplePayPayment(
                new ArrayObject([
                    'domainName' => $command->domainName,
                    'displayName' => $command->displayName,
                    'validationUrl' => $command->validationUrl,
                ]),
                $output = new ArrayObject(),
            ),
        );

        Assert::string($output['result']);
        Assert::string($output['session']);

        return new InitializeApplePaySessionResult(
            $output['result'],
            $output['session'],
        );
    }

    private function verifyOrderExist(string $orderToken): void
    {
        $order = $this->orderRepository->findOneByTokenValue($orderToken);

        if (null === $order) {
            throw new OrderCannotBeFoundException(sprintf('Order with token "%s" cannot be found.', $orderToken));
        }
    }
}
