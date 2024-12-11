<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Factory\GatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Factory\GetTpayTransactionsChannelsFactoryInterface;
use Payum\Core\Payum;
use Psr\Log\LoggerInterface;
use Tpay\OpenApi\Utilities\TpayException;

final class TpayTransactionChannelResolver implements TpayTransactionChannelResolverInterface
{
    public function __construct(
        private readonly Payum $payum,
        private readonly GetTpayTransactionsChannelsFactoryInterface $getTpayTransactionsChannelsFactory,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function resolve(): array
    {
        $gateway = $this->payum->getGateway(GatewayFactory::NAME);

        $value = $this->getTpayTransactionsChannelsFactory->createNewEmpty();

        try {
            $gateway->execute($value, true);
        } catch (TpayException $e) {
            $this->logger?->critical('Unable to get banks list. TpayException thrown.', ['exceptionMessage' => $e->getMessage()]);

            return [];
        }

        $result = $value->getResult();

        if (!isset($result['result']) || 'success' !== $result['result']) {
            $this->logger?->critical('Unable to get banks list. The result is not success.', ['responseBody' => json_encode($result)]);

            return [];
        }

        if (!isset($result['channels'])) {
            $this->logger?->critical('Unable to get banks list. The channels key is missing.', ['responseBody' => json_encode($result)]);

            return [];
        }

        $indexedResult = [];
        foreach ($result['channels'] as $tpayTransactionChannel) {
            $indexedResult[$tpayTransactionChannel['id']] = $tpayTransactionChannel;
        }

        return $indexedResult;
    }
}
